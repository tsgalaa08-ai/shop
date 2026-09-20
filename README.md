# ЖИЖИГ SHOP

Монгол хэл дээрх жижиг онлайн дэлгүүрийн MVP. `backend` нь Laravel 13 / PHP 8.4 REST API, `frontend` нь Next.js 16 / React / TypeScript / Tailwind UI юм. Guest checkout, stock locking, банкны шилжүүлэг, Sanctum admin token, QPay service abstraction бүгд API-гаар холбогдсон.

## Шаардлага

- PHP 8.3+, Composer, Node.js 20+, MySQL 8
- Local development-д SQLite ашиглаж болно; production-д MySQL тохируулна.

## Суулгах

```bash
cd backend
cp .env.example .env
composer install
php artisan key:generate
php artisan storage:link
php artisan migrate --seed

cd ../frontend
npm install
```

`backend/.env` дотор database, `APP_URL`, QPay болон filesystem тохируулна. Frontend-д шаардлагатай бол `frontend/.env.local` үүсгээд `NEXT_PUBLIC_API_URL=http://localhost:8000/api` гэж өгнө.

## Local ажиллуулах

```bash
# terminal 1
cd backend && php artisan serve --host=0.0.0.0 --port=8000
# terminal 2
cd frontend && npm run dev
```

Нүүр: `http://localhost:3000`, API: `http://localhost:8000/api`. Demo admin: `admin@example.com` / `ChangeMe123!`; production-д seed password-ийг нэн даруй солино.

## API

`GET /api/products`, `GET /api/products/{slug}`, `GET /api/categories`, `POST /api/orders`, `GET /api/orders/{orderNumber}`, `POST /api/qpay/create`, `POST /api/qpay/check`. Sanctum token шаарддаг admin endpoints нь `/api/admin/products`, `/api/admin/orders`, `/api/admin/payments/{payment}`.

Order creation нь validation, transaction, `lockForUpdate`, server-side price calculation, stock decrement, order item/payment creation-ийг нэг transaction-д хийдэг. Үнэ болон stock-ийг client-д итгэхгүй.

## Test / build

```bash
cd backend && php artisan test
cd ../frontend && npm run lint && npm run build
```

## Production deployment (Ubuntu + Nginx)

```bash
sudo mkdir -p /var/www/shop
sudo chown -R $USER:$USER /var/www/shop
git clone <repository> /var/www/shop
cd /var/www/shop/backend && composer install --no-dev --optimize-autoloader
php artisan migrate --force && php artisan storage:link
cd ../frontend && npm ci && npm run build
```

Laravel-ийн `public` directory-г PHP-FPM руу, Next.js-ийг Node process (`npm run start -- -p 3000`) болгон supervisor/systemd-ээр ажиллуулна. Nginx нь `/api`-г `127.0.0.1:8000` руу, бусад хүсэлтийг Next.js `127.0.0.1:3000` руу proxy хийнэ. `APP_DEBUG=false`, HTTPS, secure cookies, MySQL least-privilege user, firewall болон log rotation заавал тохируулна.

### Nginx гол тохиргоо

```nginx
location /api/ { proxy_pass http://127.0.0.1:8000; proxy_set_header Host $host; proxy_set_header X-Real-IP $remote_addr; }
location / { proxy_pass http://127.0.0.1:3000; proxy_set_header Host $host; proxy_set_header X-Forwarded-Proto $scheme; }
```

## Security notes

Laravel validation, Eloquent parameter binding, hashed passwords, Sanctum authorization, rate limiting, MIME/size-limited random storage filenames, transaction + row lock ашиглана. `.env` git-д орохгүй. QPay credentials зөвхөн environment-д байна; `QPAY_ENABLED=false` үед demo invoice contract ажиллана.
