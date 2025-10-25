# Laravel Large Request Handler

A Laravel application designed to track and log HTTP requests for monitoring and analysis purposes. This project provides comprehensive request tracking capabilities with detailed logging of request metadata, user information, and system interactions.

## 🏗️ Architecture

### Core Components

The application follows a clean architecture pattern with the following key components:

#### 1. **Request Tracking Middleware** (`TrackRequests`)
- **Location**: `app/Http/Middleware/TrackRequests.php`
- **Purpose**: Intercepts all incoming HTTP requests
- **Functionality**: Automatically triggers request tracking for every request

#### 2. **Request Tracker Service** (`RequestTracker`)
- **Location**: `app/Services/RequestTracker.php`
- **Purpose**: Core service responsible for collecting and storing request data
- **Features**:
  - Collects request URL, method, headers, and body
  - Captures client IP and user agent
  - Records authenticated user information
  - Stores data in structured format

#### 3. **Log Model** (`Log`)
- **Location**: `app/Models/Log.php`
- **Purpose**: Eloquent model for database interactions
- **Features**:
  - Handles JSON casting for request and metadata fields
  - Provides clean interface for log data access

#### 4. **Service Provider** (`AppServiceProvider`)
- **Location**: `app/Providers/AppServiceProvider.php`
- **Purpose**: Registers the RequestTracker as a singleton service
- **Configuration**: Ensures single instance across the application

### Data Flow

```
HTTP Request → TrackRequests Middleware → RequestTracker Service → Database (logs table)
```

1. **Request Interception**: Middleware captures all incoming requests
2. **Data Collection**: RequestTracker extracts comprehensive request details
3. **Data Storage**: Information is persisted to the `logs` database table
4. **Service Registration**: Singleton pattern ensures efficient resource usage

## 📊 Data Structure

### Log Entry Format

Each logged request contains:

```json
{
  "request": {
    "url": "https://example.com/api/endpoint",
    "method": "POST",
    "headers": {
      "Content-Type": "application/json",
      "Authorization": "Bearer token"
    },
    "body": {
      "key": "value"
    }
  },
  "metadata": {
    "ip": "192.168.1.1",
    "user_agent": "Mozilla/5.0...",
    "user_id": 123,
    "user_name": "John Doe",
    "user_email": "john@example.com"
  },
  "time": 1640995200
}
```

## 🚀 Setup and Installation

### Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js and npm
- Database (MySQL, PostgreSQL, SQLite)

### Installation Steps

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd lv-large-req-handle
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database setup**
   ```bash
   # Create the logs table migration first
   php artisan make:migration create_logs_table
   ```
   
   Add the following to your migration:
   ```php
   Schema::create('logs', function (Blueprint $table) {
       $table->id();
       $table->json('request');
       $table->json('metadata');
       $table->integer('time');
       $table->timestamps();
   });
   ```

6. **Run migrations**
   ```bash
   php artisan migrate
   ```

7. **Build assets**
   ```bash
   npm run build
   ```

### Quick Setup (Using Composer Scripts)

The project includes convenient setup scripts:

```bash
# Complete setup in one command
composer run setup

# Development environment with hot reload
composer run dev

# Run tests
composer run test
```

## 🔧 Configuration

### Middleware Registration

The `TrackRequests` middleware is registered in `bootstrap/app.php`:

```php
$middleware->alias([
    'track.requests' => TrackRequests::class,
]);
```

### Service Registration

The `RequestTracker` service is registered as a singleton in `AppServiceProvider`:

```php
$this->app->singleton(RequestTracker::class);
```

### Environment Variables

Configure your `.env` file with appropriate database settings:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel
DB_USERNAME=root
DB_PASSWORD=
```

## 📝 Usage

### Automatic Request Tracking

Once installed, the application automatically tracks all HTTP requests. No additional configuration is required.

### Manual Request Tracking

You can manually track requests in your controllers:

```php
use App\Services\RequestTracker;

class ApiController extends Controller
{
    public function __construct(
        protected RequestTracker $requestTracker
    ) {}

    public function store(Request $request)
    {
        // Your business logic here
        
        // Manually track specific requests if needed
        $this->requestTracker->trackRequest($request);
        
        return response()->json(['success' => true]);
    }
}
```

### Accessing Log Data

Query logged requests using the Log model:

```php
use App\Models\Log;

// Get all logs
$logs = Log::all();

// Get logs for specific user
$userLogs = Log::whereJsonContains('metadata->user_id', 123)->get();

// Get logs for specific endpoint
$endpointLogs = Log::whereJsonContains('request->url', 'api/users')->get();
```

## 🧪 Testing

Run the test suite:

```bash
# Run all tests
composer run test

# Run specific test
php artisan test --filter=ExampleTest
```

## 📈 Monitoring and Analysis

### Log Analysis Queries

Common queries for analyzing request patterns:

```php
// Most frequent endpoints
$frequentEndpoints = Log::selectRaw('JSON_EXTRACT(request, "$.url") as url, COUNT(*) as count')
    ->groupBy('url')
    ->orderBy('count', 'desc')
    ->get();

// User activity
$userActivity = Log::selectRaw('JSON_EXTRACT(metadata, "$.user_id") as user_id, COUNT(*) as requests')
    ->whereNotNull('metadata->user_id')
    ->groupBy('user_id')
    ->get();

// Request methods distribution
$methodDistribution = Log::selectRaw('JSON_EXTRACT(request, "$.method") as method, COUNT(*) as count')
    ->groupBy('method')
    ->get();
```

## 🔒 Security Considerations

- **Sensitive Data**: Consider filtering sensitive information (passwords, tokens) before logging
- **Data Retention**: Implement log rotation and cleanup policies
- **Access Control**: Ensure proper authentication for log viewing interfaces
- **GDPR Compliance**: Consider data privacy regulations when logging user information

## 🛠️ Development

### Adding Custom Tracking

Extend the `RequestTracker` service to add custom tracking logic:

```php
class CustomRequestTracker extends RequestTracker
{
    public function trackRequest($request)
    {
        // Add custom tracking logic
        $customData = $this->collectCustomData($request);
        
        // Call parent method
        parent::trackRequest($request);
        
        // Add custom data to logs
        $this->addCustomData($customData);
    }
}
```

### Performance Optimization

For high-traffic applications:

1. **Batch Processing**: Modify `storeInDatabase()` to batch insert multiple logs
2. **Queue Integration**: Use Laravel queues for asynchronous log processing
3. **Database Indexing**: Add indexes on frequently queried JSON fields
4. **Log Rotation**: Implement automatic log cleanup

## 📚 API Documentation

### RequestTracker Service

#### Methods

- `trackRequest(Request $request)`: Track an incoming HTTP request
- `storeInDatabase()`: Persist accumulated logs to database

#### Dependencies

- `Illuminate\Support\Facades\DB`: Database operations
- `Illuminate\Http\Request`: HTTP request handling

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Submit a pull request

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🆘 Support

For issues and questions:

1. Check the existing issues on GitHub
2. Create a new issue with detailed description
3. Include relevant logs and error messages

## 🔄 Version History

- **v1.0.0**: Initial release with basic request tracking functionality
- **v1.1.0**: Added user authentication tracking
- **v1.2.0**: Enhanced metadata collection and JSON casting

---

**Note**: This application is designed for development and testing environments. For production use, consider implementing additional security measures, performance optimizations, and monitoring solutions.
