order-api/
├── app/
│   ├── Contracts/
│   │   └── MiddlewareInterface.php              ← TV2 (Interface)
│   ├── Exceptions/
│   │   └── Handler.php                          ← TV5 (Error handling tập trung)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Controller.php                   ← Laravel có sẵn
│   │   │   ├── BaseController.php               ← TV2 (Abstract Class)
│   │   │   └── Api/
│   │   │       ├── AuthController.php           ← TV5 (JWT login/register/logout)
│   │   │       └── OrderController.php          ← TV4 (CRUD + confirm/cancel)
│   │   ├── Middleware/
│   │   │   ├── JwtAuthMiddleware.php            ← TV5 (Authentication — 401)
│   │   │   ├── OrderOwnerMiddleware.php         ← TV5 (Authorization — 403)
│   │   │   └── CorsMiddleware.php               ← TV5 (CORS headers)
│   │   ├── Requests/
│   │   │   ├── StoreOrderRequest.php            ← TV2 (Form Request Validation)
│   │   │   └── UpdateOrderRequest.php           ← TV2 (Form Request Validation)
│   │   ├── Resources/
│   │   │   └── OrderResource.php                ← TV2 (API Resource — View layer)
│   │   └── Responses/
│   │       └── ApiResponse.php                  ← TV2 (Static Factory Pattern)
│   └── Models/
│       ├── User.php                             ← TV1 (implements JWTSubject)
│       └── Order.php                            ← TV1 (Eloquent + scopes + business logic)
├── bootstrap/
│   └── app.php                                  ← TV3 (đăng ký middleware aliases)
├── config/
│   ├── cors.php                                 ← TV3 (cấu hình CORS)
│   └── jwt.php                                  ← TV3 (cấu hình JWT — auto-publish)
├── database/
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php   ← Laravel có sẵn
│   │   └── xxxx_xx_xx_create_orders_table.php         ← TV1 (Migration)
│   └── seeders/
│       ├── DatabaseSeeder.php                   ← Laravel có sẵn
│       └── OrderSeeder.php                      ← TV1 (Dữ liệu mẫu)
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── app.blade.php                    ← TV3 (Layout chính — @yield)
│       ├── orders/
│       │   ├── index.blade.php                  ← TV4 (Danh sách — @foreach)
│       │   ├── show.blade.php                   ← TV4 (Chi tiết — @if/@elseif)
│       │   └── _order-card.blade.php            ← TV1 (Partial — @include)
│       └── components/
│           └── status-badge.blade.php           ← TV2 (Component — @props)
├── routes/
│   ├── api.php                                  ← TV3 (API routes — JWT protected)
│   └── web.php                                  ← TV3 (Web routes — Blade views)
├── public/
│   └── index.php                                ← Laravel có sẵn (Front Controller)
├── .env                                         ← TV3 (DB + JWT_SECRET config)
├── .env.example                                 ← Laravel có sẵn
├── composer.json                                ← Laravel có sẵn + tymon/jwt-auth
└── README.md                                    ← Cả team
