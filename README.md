# alisms-for-laravel
laravel最新的阿里云短信接口
# 安装
````
composer require xiaoyi/ali
````

# 设置配置文件
1. 在 `config/app.php` 注册 ServiceProvider 和 Facade

```php
'providers' => [
    // ...
    Xiaoyi\Ali\AlismsServiceProvider::class
],
'aliases' => [
    // ...
    'Alisms' => Xiaoyi\Ali\Facades\Alisms::class,
],
```

2、创建配置文件：
````
php artisan vendor:publish --provider="XiaoYi\Ali\AlismsServiceProvider"
````
3. 修改应用根目录下的 `config/ali.php` 中对应的参数即可。

