<?php
/*
 * File model
 *
 * @author Andrey Glukhov <greeffff@yandex.ru>
 */
namespace Avglukh\Fileuploader\Models;

use Illuminate\Database\Eloquent\Model;

class FileuploaderFile extends Model
{

    protected $guarded = [];

    /**
     * @return int|mixed
     */
    static public function lastIndex(){

        return !is_null(self::orderByDesc('index')->first()) ? self::orderByDesc('index')->first()->index+1 : 0 ;

    }

    /**
     * @param $name
     * @return FileuploaderFile|Model|null
     */
   static public function getFile($name){

        return self::where('name',$name)->first();

    }

    /**
     * @return string
     */
    public function getFullPathAttribute(){

        return '/'.$this->file;

    }

    public function getOriginalName(){

//        return str_replace()

    }

    /**
     * @return string
     */
    public function getStoragePathAttribute(){

        return storage_path('/app/public/'.$this->hash_name);

    }

    /**
     * @return string|string[]
     */
    public function getHashNameAttribute(){

        return str_replace('storage/','',$this->file);

    }

}
