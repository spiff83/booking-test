<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $fillable = ['key','value'];

    public static function getBool(string $key, bool $default=false): bool {
        $v = static::where('key',$key)->value('value');
        if (is_null($v)) return $default;
        return in_array(strtolower($v), ['1','true','yes','on'], true);
    }

    public static function getInt(string $key, int $default=0): int {
        $v = static::where('key',$key)->value('value');
        return is_null($v) ? $default : (int)$v;
    }

    public static function set(string $key, $value): void {
        static::updateOrCreate(['key'=>$key], ['value'=>(string)$value]);
    }
}
