<?php
/*
 * FileUploaderService with default methods from FileUploader Gallery
 *
 * @author Andrey Glukhov <greeffff@yandex.ru>
 */

namespace Avglukh\Fileuploader\Services;


use Avglukh\Fileuploader\Models\FileuploaderFile;

class FileUploaderService
{
    /**
     * @param $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload($request){

        $file = $request->file()['files'][0];
        $mime = $file->getMimeType();

        if (strstr($mime, 'image/')) {
            $format = 'image';
        } elseif (strstr($mime, 'video/')) {
            $format = 'video';
        } elseif (strstr($mime, 'audio/')) {
            $format = 'audio';
        }
        $publicPath = $file->store('public');
        $path = 'storage/' . pathinfo($publicPath)['basename'];

        FileuploaderFile::updateOrCreate([
            'name' => pathinfo($publicPath)['basename'],
            'file' => $path,
            'index' => FileuploaderFile::lastIndex(),
            'size' => $file->getSize(),
            'type' => $mime,
        ]);

        $data['hasWarnings'] = false;
        $data['isSuccess'] = true;
        $data['warnings'] = [];

        $data['files'][0] = [
            'date' => \date('D. j M Y H:i:s'),
            'extension' => $file->getClientOriginalExtension(),
            'file' => $path,
            'format' => $format,
            'name' =>pathinfo($publicPath)['basename'],
            'old_name' => $file->getClientOriginalName(),
            'old_title' => pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'replaced' => false,
            'size' => $file->getSize(),
            'size2' => $this->humanFileSize($file->getSize()),
            'title' => pathinfo(pathinfo($publicPath)['basename'], PATHINFO_FILENAME),
            'type' => $mime,
            'uploaded' => true,
        ];

        return response()->json($data)->header('Content-Type:', 'text/html; charset=UTF-8');

    }

    /**
     * @param $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function preload($request){

        $value = [];
        $files = FileuploaderFile::orderBy('index','asc')->get();

        foreach ($files as $file){

            $value[$file->index] =[
                'name'=> $file->name,
                'type' => $file->type,
                'size' => $file->size,
                'file' => $file->full_path,
                'data' => [
                    'date' => date($file->created_at),
                    'isMain' => $file->is_main,
                    'thumbnail' => $file->full_path,
                    'url' => $file->full_path,
                    'listProps' => [
                        'id' =>$file->id,
                    ]
                ],
            ];

        }

        return response()->json(array_values($value))->header('Content-Type','text/html; charset=UTF-8');

    }

    /**
     * @param $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function resize($request){

        $image = FileuploaderFile::findOrfail($request->id);
        $path = storage_path('/app/public/'.$image->hash_name);
        $img = $this->createImage($path);

        $editor = json_decode($request->_editor);

        if($editor->rotation > 0 || isset($editor->crop)) {
            $im = imagerotate($img, -$editor->rotation,0);
            imagejpeg($im, $path);

            if(isset($editor->crop)){
                $im = imagecrop($im, ['x' => $editor->crop->left, 'y' => $editor->crop->top, 'width' => $editor->crop->width, 'height' => $editor->crop->height]);
                imagejpeg($im, $path);
            }
        }

        $file = FileuploaderFile::getFile($request->name);
        $file->save();

        return response()->json(['success'=>true]);

    }

    /**
     * @param $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function rename($request){

        $file = FileuploaderFile::getFile($request->name);
        $file->name = $request->title.'.'.pathinfo(storage_path($file->hash_name))['extension'];
        $file->save();

        return response()->json(['title'=>$request->title])->header('Content-Type','text/html; charset=UTF-8');

    }

    /**
     * @param $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function asmain($request){

        FileuploaderFile::query()->update(['is_main'=>0]);

        $file = FileuploaderFile::findOrfail($request->id);
        $file->is_main = true;
        $file->save();

        return response()->json(['title'=>$file->name])->header('Content-Type','text/html; charset=UTF-8');

    }

    /**
     * @param $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sort($request){

        $sortList = collect(json_decode($request->list));
        $file = FileuploaderFile::get(['id','index']);

        $file->map(function ($value,$key) use($sortList){
            $value->index = $sortList->where('id',$value->id)->first()->index;
            $value->save();
        });

        return response()->json(['success'=>true]);

    }

    /**
     * @param $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function remove($request){

        $column = array_values(array_flip(FileuploaderFile::get(['index'])->pluck('index')->toArray()));

        $image = FileuploaderFile::findOrfail($request->id);
        unlink($image->storage_path);
        $image->delete();

        $files = FileuploaderFile::all();
        $files = $files->map(function ($value,$key) use($column){
            $value->index = $column[$key];
            $value->save();
            return $value;
        });

        return response()->json(['success'=>true]);

    }

    /**
     * @param $size
     * @param string $unit
     * @return string
     */
    function humanFileSize($size,$unit="") {

        if( (!$unit && $size >= 1<<30) || $unit == "GB")
            return number_format($size/(1<<30),2)."GB";
        if( (!$unit && $size >= 1<<20) || $unit == "MB")
            return number_format($size/(1<<20),2)."MB";
        if( (!$unit && $size >= 1<<10) || $unit == "KB")
            return number_format($size/(1<<10),2)."KB";
        return number_format($size)." bytes";

    }

    /**
     * @param $path
     * @return false|resource
     */
    function createImage($path){

        $size = getimagesize($path);

        if ($size) {
            list($w, $h, $t) = $size;
        } else {
            dd('none');
        }

        switch ($t) {
            case 1:
                $img = imagecreatefromgif($path);
                break;
            case 2:
                $img = imagecreatefromjpeg($path);
                break;
            case 3:
                $img = imagecreatefrompng($path);
                break;
            case IMAGETYPE_BMP:
                $img = ImageHelper::ImageCreateFromBMP($path);
                break;
            case IMAGETYPE_WBMP:
                $img = imagecreatefromwbmp($path);
                break;
            default:
                dd('123');
        }

        return $img;

    }

}