<?php
/*
 * Controller of the fileuploader.js,
 * implemented standard methods of
 * the file uploader file
 * that are stored in the service
 *
 * @author Andrey Glukhov <greeffff@yandex.ru>
 */
namespace Avglukh\Fileuploader\Http\Controllers;

use App\Http\Controllers\Controller;
use Avglukh\Fileuploader\Models\FileuploaderFile;
use Avglukh\Fileuploader\Services\FileUploaderService;
use Illuminate\Http\Request;


class FileUploaderController extends Controller
{

    protected $fileuploaderService;

    /**
     * Construct with object instance
     * @param FileUploaderService $fileuploaderService
     */
    public function __construct(FileUploaderService $fileuploaderService)
    {
        $this->fileuploaderService = $fileuploaderService;
    }

    /**
     * Checker of existing in object instance methods from request and return them
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request){

        if(method_exists($this->fileuploaderService, $request->type)){
            return call_user_func_array([$this->fileuploaderService , $request->type],[$request]);
        }

    }

}
