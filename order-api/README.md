```
order-api/
├── app/
│   ├── Contracts/
│   │   └── MiddlewareInterface.php              # TV2 — Interface contract
│   ├── Exceptions/
│   │   └── Handler.php                          # TV5 — Error handling tập trung
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Controller.php                   # Laravel mặc định
│   │   │   ├── BaseController.php               # TV2 — Abstract Class
│   │   │   ├── OrderWebController.php           # TV4 — Controller cho Blade views
│   │   │   └── Api/
│   │   │       ├── AuthController.php           # TV5 — JWT login/register/logout + listUsers
│   │   │       └── OrderController.php          # TV4 — CRUD + confirm/cancel/ship/deliver + stats
│   │   ├── Middleware/
│   │   │   ├── JwtAuthMiddleware.php            # TV5 — Authentication (401)
│   │   │   ├── OrderOwnerMiddleware.php         # TV5 — Authorization (403)
│   │   │   ├── CorsMiddleware.php               # TV5 — CORS headers
│   │   │   └── AdminMiddleware.php              # TV5 — Phân quyền Admin (403)
│   │   ├── Requests/
│   │   │   ├── StoreOrderRequest.php            # TV2 — Form Request Validation
│   │   │   └── UpdateOrderRequest.php           # TV2 — Form Request Validation
│   │   ├── Resources/
│   │   │   └── OrderResource.php                # TV2 — API Resource (View layer)
│   │   └── Responses/
│   │       └── ApiResponse.php                  # TV2 — Static Factory Pattern
│   └── Models/
│       ├── User.php                             # TV1 — JWTSubject + isAdmin()
│       └── Order.php                            # TV1 — Eloquent + scopes + logic
├── bootstrap/
│   └── app.php                                  # TV3+TV5 — Middleware aliases + exception handler
├── config/
│   ├── cors.php                                 # TV3 — Cấu hình CORS
│   └── jwt.php                                  # TV3 — Cấu hình JWT
├── database/
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_users_table.php       # Laravel mặc định
│   │   ├── 2026_05_03_000000_create_orders_table.php      # TV1 — Bảng orders
│   │   └── 2026_05_06_000001_add_role_to_users_table.php  # TV5 — Thêm cột role
│   └── seeders/
│       ├── DatabaseSeeder.php                   # Laravel mặc định
│       └── OrderSeeder.php                      # TV1 — Dữ liệu mẫu
├── resources/views/
│   ├── layouts/
│   │   └── app.blade.php                        # TV3 — Layout chính
│   ├── orders/
│   │   ├── index.blade.php                      # TV4 — Danh sách đơn hàng
│   │   └── show.blade.php                       # TV4 — Chi tiết đơn hàng
│   └── components/
│       └── status-badge.blade.php               # TV2 — Component badge trạng thái
├── routes/
│   ├── api.php                                  # TV3 — API routes (public + JWT + admin)
│   └── web.php                                  # TV3 — Web routes (Blade views)
├── public/
│   └── index.php                                # Laravel — Front Controller
├── .env                                         # Cấu hình DB + JWT_SECRET
├── composer.json                                # Dependencies
├── artisan                                      # Laravel CLI
└── README.md
```
