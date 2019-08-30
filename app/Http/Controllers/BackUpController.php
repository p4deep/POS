<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Storage;

class BackUpController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (!auth()->user()->can('backup')) {
            abort(403, 'Unauthorized action.');
        }

        return view ('backup.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!auth()->user()->can('backup')) {
            abort(403, 'Unauthorized action.');
        }

        //Disable in demo
        if(config('app.env') == 'demo'){
            $output = ['success' => 0, 
                            'msg' => 'Feature disabled in demo!!'
                        ];
            return back()->with('status', $output);
        }

        try {
            Artisan::call('backup:run', ['--only-to-disk' => 'local']);

            $name = str_replace(" ", '-', config('backup.backup.name'));
            $disk_type = 'local';

            $disk = Storage::disk($disk_type);
            $files = $disk->files($name);

            $backups = [];

            // make an array of backup files, with their filesize and creation date
            foreach ($files as $k => $f) {
                // only take the zip files into account
                if (substr($f, -4) == '.zip' && $disk->exists($f)) {
                    $backups[] = [
                        'file_path' => $f,
                        'file_name' => str_replace(config('backup.backup.name') . '/', '', $f)
                    ];
                }
            }

            // reverse the backups, so the newest one would be on top
            $backups = array_reverse($backups);

            if(!empty($backups[0]['file_name'])){
                $file = $backups[0]['file_name'];
                $disk = Storage::disk($disk_type);
                
                if ($disk->exists($file)) {
                    
                    $fs = Storage::disk($disk_type)->getDriver();
                    $stream = $fs->readStream($file);

                    return \Response::stream(function () use ($stream) {
                        fpassthru($stream);
                    }, 200, [
                        "Content-Type" => $fs->getMimetype($file),
                        "Content-Length" => $fs->getSize($file),
                        "Content-disposition" => "attachment; filename=\"" . basename($file) . "\"",
                    ]);
                } else {
                    
                    $output = ['success' => 0, 
                            'msg' => __('lang_v1.backup_doesnt_exist')
                        ];
                    return back()->with('status', $output);
                }
            }
        } catch(Exception $e){
            $output = ['success' => 0, 
                        'msg' => $e->getMessage()
                    ];
            return back()->with('status', $output);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!auth()->user()->can('backup')) {
            abort(403, 'Unauthorized action.');
        }
    }
}
