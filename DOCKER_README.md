# Running HelloChef with Docker

## Quick start

1. **Clone and start**
```bash
git clone https://github.com/AhmedElMenyawi/product-engineer-be-challenge.git
cd product-engineer-be-challenge
docker-compose up --build
```

2. **Run the seeders (IMPORTANT!)**
```bash
docker-compose exec app php artisan db:seed
```

3. **Done!**
- API: http://localhost:8080
- MySQL: localhost:3307

## Test users (after running seeders)

| Email | Password |
|-------|----------|
| `anthonyponcio@hellchef.com` | `AP@Random123` |
| `mohsinali@hellchef.com` | `MA@Random123` |
| `ahmedelmenyawi@hellchef.com` | `AE@Random123` |

## Other useful commands

```bash
# Stop the app
docker-compose down

# Run migrations
docker-compose exec app php artisan migrate

# Run tests
docker-compose exec app php artisan test
```

That's it! 