<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RequestTracker
{
    protected $logs = [];
    protected $batchSize = 10; // Insert logs in batches for better performance

    /**
     * Track an incoming HTTP request and log its details.
     *
     * This method collects various details from the given request, organizes them
     * into a structured log entry, and pushes the entry into the internal logs array.
     * It then persists the collected log(s) into the database in batches.
     *
     * The details collected for each request include:
     * - Request URL and HTTP method
     * - All HTTP headers and body parameters
     * - Client IP address and user agent string
     * - Authenticated user details (id, name, email) if available
     * - Current timestamp
     *
     * @param \Illuminate\Http\Request $request The incoming HTTP request to track.
     * @return void
     */
    public function trackRequest($request)
    {
        $this->logs[] = [
            'request' => [
                'url'     => $request->url(),
                'method'  => $request->method(),
                'headers' => $request->headers->all(),
                'body'    => $request->all(),
            ],
            'metadata' => [
                'ip'         => $request->ip() ,
                'user_agent' => $request->userAgent(),
                'user_id'    => $request->user()?->id,
                'user_name'  => $request->user()?->name,
                'user_email' => $request->user()?->email,
            ],
            'time' => now(),
        ];

        // Only write to database when batch size is reached
        // For immediate logging, write on every request
        $this->storeInDatabase();
    }

    /**
     * Persist accumulated request log entries into the database.
     *
     * This method inserts all entries stored in the internal $logs array
     * into the request_logs database table. After successful insertion,
     * it resets the $logs array to ensure no duplicate entries are inserted.
     *
     * Assumes each entry in $logs is formatted to match the columns of the
     * request_logs table. This method is called internally after tracking
     * each request.
     *
     * @return void
     */
    protected function storeInDatabase()
    {
        Log::info('Storing logs in database', ['logs' => $this->logs]);

        $formattedLogs = array_map(function ($log) {
            return [
                'request' => json_encode($log['request']),
                'metadata' => json_encode($log['metadata']),
                'time' => $log['time'],
                'created_at' => now(),
                'updated_at' => now()
            ];
        }, $this->logs);

        DB::table('logs')->insert($formattedLogs);

        $this->logs = [];
    }
}
