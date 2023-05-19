# คู่มือการติดตั้ง Laravel Sanctum สำหรับ Single Page Application (SPA) ที่ใช้ระบบการเข้าสู่ระบบด้วย Token

คู่มือนี้จะให้คำแนะนำวิธีการติดตั้ง Laravel Sanctum และสร้าง Single Page Application (SPA) ที่มีระบบการเข้าสู่ระบบด้วย Token โดยใช้ PHP พวกเราจะตั้งค่า Laravel Sanctum สำหรับการรับรองความถูกต้องด้วย Token ของ API จากนั้นสร้างระบบการเข้าสู่ระบบใน SPA

## ข้อกำหนดเบื้องต้น

- PHP 7.3 หรือสูงกว่า
- ติดตั้ง Composer แบบส่วนกลาง
- Laravel Framework 7.0 หรือสูงกว่า
- MySQL หรือฐานข้อมูล SQL อื่น ๆ

## คู่มือทีละขั้นตอน

1. **ติดตั้ง Laravel Sanctum**

   Laravel Sanctum สามารถติดตั้งผ่าน Composer รันคำสั่งต่อไปนี้ใน Terminal ของคุณ:

   ```
   composer require laravel/sanctum
   ```

2. **เผยแพร่ไฟล์การกำหนดค่าและการย้ายของ Sanctum**

   หลังจากที่แพ็คเกจถูกติดตั้ง คุณควรเผยแพร่ไฟล์การกำหนดค่าและการย้ายของ Sanctum โดยใช้คำสั่ง `vendor:publish` ของ Artisan คำสั่งนี้จะเผยแพร่ไฟล์การกำหนดค่า `sanctum` ของ Sanctum ไปยังไดเรกทอรี `config` ของคุณ:

   ```
   php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
   ```

   นี่จะเผยแพร่การย้ายของตาราง `personal_access_tokens` ไปยังไดเรกทอรี `database/migrations` ของคุณ

3. **รัน Migrations**

   รัน migrations ด้วยคำสั่งต่อไปนี้:

   ```
   php artisan migrate
   ```

4. **เพิ่ม middleware ของ Sanctum**

   ในกลุ่ม middleware `api` ภายในไฟล์ `app/Http/Kernel.php` ของแอปพลิเคชันของคุณ ให้เพิ่ม middleware `EnsureFrontendRequestsAreStateful::class`:

   ```php
   'api' => [
       \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
       'throttle:60,1',
       \Illuminate\Routing\Middleware\SubstituteBindings::class,
   ],
   ```

5. **ใช้ trait `HasApiTokens`**

   ต่อไป ควรใช้ trait `HasApiTokens` ในโมเดล `App\Models\User` ของคุณ Trait นี้จะให้บางเมธอดช่วยเหลือแก่โมเดลของคุณ ซึ่งช่วยให้คุณตรวจสอบ token และความสามารถของผู้ใช้ที่ผ่านการตรวจสอบแล้ว:

   ```php
   namespace App\Models;

   use Illuminate\Foundation\Auth\User as Authenticatable;
   use Laravel\Sanctum\HasApiTokens;

   class User extends Authenticatable
   {
       use HasApiTokens, Notifiable;
   }
   ```

6. **สร้างจุดสิ้นสุดการเข้าสู่ระบบ**

   คุณสามารถสร้างจุดสิ้นสุดการเข้าสู่ระบบในไฟล์ `routes/api.php` ของคุณ นี่จะใช้เมธอด `attempt` เพื่อตรวจสอบข้อมูลประจำตัวผู้ใช้ ถ้าข้อมูลประจำตัวผู้ใช้ถูกต้อง เราจะส่งกลับ token ที่สามารถใช้สำหรับคำขอที่ผ่านการตรวจสอบ

   ```php
   use Illuminate\Http\Request;
   use Illuminate\Support\Facades\Route;
   use App\Models\User;
   use Illuminate\Support\Facades\Hash;

   Route::post('/login', function (Request $request) {
       $user = User::where('email', $request->email)->first();

       if (! $user || ! Hash::check($request->password, $user->password)) {
           return response([
               'message' => ['These credentials do not match our records.']
           ], 404);
       }

       $token = $user->createToken('my-app-token')->plainTextToken;

       $response = [
           'user' => $user,
           'token' => $token,
       ];

       return response($response, 201);
   });
   ```

7. **ใช้ token**

   ตอนนี้คุณสามารถใช้ token ที่คุณได้รับจากจุดสิ้นสุดการเข้าสู่ร

บบเพื่อทำคำขอที่ผ่านการตรวจสอบไปยัง API ของคุณ คุณควรรวมมันในส่วน `Authorization` ของ header ดังนี้:

```vbnet
Authorization: Bearer <token>
```

เรียบร้อยแล้ว! ตอนนี้คุณก็พร้อมใช้ Laravel Sanctum ในการสร้างระบบการเข้าสู่ระบบด้วย Token สำหรับ Single Page Application (SPA) ของคุณ.
