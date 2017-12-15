<?php
/**
 * Created by PhpStorm.
 * User: hw
 * Date: 2017/12/15
 * Time: 10:28
 */
namespace Xiaoyi\Ali\Facades;
use Illuminate\Support\Facades\Facade;

class Alisms extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'alisms';
    }
}