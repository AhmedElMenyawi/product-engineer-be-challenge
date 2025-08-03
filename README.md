# Hello Chef Task Management API

A robust task management system built with Laravel that tracks "Who is doing What, and When" with comprehensive audit trails and performance optimizations.

## Features

- **Task Management**: Create, read, update, delete tasks with unique identifiers
- **User Management**: Authentication and authorization with Laravel Sanctum
- **Team Organization**: Tasks can be assigned to specific teams
- **Audit Trails**: Complete history tracking of all task changes
- **Bulk Operations**: Create multiple tasks at once
- **Analytics**: Status summaries and filtering capabilities
- **Performance Optimized**: Database indexes and async processing
- **Comprehensive Testing**: 98.3% test coverage (66/67 tests passing)

## Performance Optimizations

### Database Indexes
- **13 indexes** on tasks table for fast filtering and queries
- **7 indexes** on task_histories table for efficient audit trail lookups
- **Composite indexes** for common query patterns (team + status, assigned + status, etc.)

### Async Processing
- **Task History Logging**: All task changes are logged asynchronously using Laravel jobs


## Setup

### Requirements
- PHP 8.1+
- Laravel 10+
- MySQL 8.0+
- Composer

### Installation
```bash
# Clone the repository
git clone https://github.com/AhmedElMenyawi/product-engineer-be-challenge.git
cd product-engineer-be-challenge

# Install dependencies
composer install

# Copy environment file
cp .env.example .env

# Configure database in .env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hellochef_tasks
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Run tests
php artisan test
```

### Async Processing Setup

## API Endpoints

### Authentication
- `POST /api/v1/login` - User login
- `POST /api/v1/logout` - User logout

### Tasks
- `GET /api/v1/tasks` - List tasks with filtering and pagination
- `POST /api/v1/tasks` - Create a new task
- `GET /api/v1/tasks/{token}` - Get task by token
- `PUT /api/v1/tasks/{token}` - Update task
- `DELETE /api/v1/tasks/{token}` - Delete task
- `POST /api/v1/tasks/bulk-create` - Create multiple tasks
- `GET /api/v1/tasks/status-summary` - Get task completion statistics

### Users
- `POST /api/v1/users` - Create a new user
- `POST /api/v1/users/bulk-create` - Create multiple users

## Testing

```bash
# Run all tests
php artisan test

# Run specific test suites
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Run performance tests
php artisan test --filter="PerformanceTest"

# Run job tests
php artisan test --filter="TaskHistoryJobTest"
```

## Performance Testing

The solution includes comprehensive performance tests that verify:
- Database query performance with 1000+ records
- Async job processing
- Index effectiveness
- API response times

## QAs:

Q: How could this be used for recurring team planning (e.g. weekly check-ins)?
A: this solution can be used to :
1- Review tasks from the previous week using the summary endpoint
2- Update status of each task of them if needed or not updated yet(one was done through the weekend)
3- Create tasks for the next week or re-assign tasks based on who is taking vacation or suddenly unavailable
4- Create new users and assign tasks if new joiners
5- Task history can be used to trace bottlenecks users are facing and tasks taking much time more than expected and act accoridngly
6- check our weekly-monthly-quarterly-yearly progress and see how we are moving on regarding count of each status to see rate of getting things done .. this can highlight an issue either on team activity or planning way so we act to enhance the output

Q:If this system were used by multiple teams, how would you ensure it remains useful but not cluttered?
A:Each task is now assigned to a specific team of course I did not include the whole team module I had in mind to keep it simple .. but a team would have a group of people under it let's say team_users table .. also a specific user can see own tasks under specific team .. team leader can see tasks of his only team no need to look into all tasks .. later on we can add user roles were some can add/edit/delete task other would only add/edit others only add and so on .. also I introduced on list the pagination to make sure data is orgainzied when the team gets big and tasks are alot .. as much as I could I would say I went in the middle of building base for scalable product also keeping the simple "who,what,when" model

Q: What signals would you look for to know if this tool is actually helping a team?
A: Would look at the completion rate as task completed vs total assigned per user or team .. also would look at the difference between task creation time and completed at time .. would look at user engaments as well if tasks are being created but not moving this needs to be looked at .. would look at the current channels we are using for example updates would have been used "is this task done?" or "any updates?" if these message rate is becoming low then we are doing good .. would do a survey after 1 month and share it with the teams and collect feedback

## Future Enhancements

- I would that later we can add a whole monthly analytical reports that would be generated for each team, manager to track progress and see 

- would have done task statuses as seperate table that can be edited by admins so that they can add new status if wanted but igonered it for the scope of the task

## NOTES 
While working on this solution, I used both Cursor and ChatGPT to help organize my thoughts, act as reviewers, and challenge my assumptions by prompting edge cases. During the unit testing phase, I intentionally avoided manual intervention to simulate having a QA engineer independently test my work. That said, I performed thorough manual testing before that to ensure core functionality was working as expected. And also rewrite my docs in a proper way for users to enjoy reading and fix any missed typos.