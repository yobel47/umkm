<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class UmkmController extends BaseController
{
    public function index()
    {
        $data = DB::select('
            SELECT 
                mu.*
            FROM 
                md_umkm mu
        ');

        foreach ($data as $key => $item) {

            $product = DB::select('
                SELECT 
                    mp.*
                FROM 
                    md_product mp
                WHERE
                    mp.id_umkm = "' . $item->id . '"
            ');

            $item->products = $product;
        }

        return response()->json($data);
    }

    public function detail($id)
    {
        $data = DB::selectOne('
            SELECT 
                mu.*
            FROM 
                md_umkm mu
            WHERE
                mu.id = "' . $id . '"
        ');

        $product = DB::select('
            SELECT 
                mp.*
            FROM 
                md_product mp
            WHERE
                mp.id_umkm = "' . $id . '"
        ');

        $data->products = $product;
        return response()->json($data);
    }


    public function search($search)
    {
        $data = DB::select('
            SELECT DISTINCT 
                mu.*
            FROM 
                md_umkm mu
            INNER JOIN md_product mp ON mu.id = mp.id_umkm
            WHERE
                mu.name LIKE "%' . $search . '%" || 
                mu.city LIKE "%' . $search . '%" || 
                mu.province LIKE "%' . $search . '%" || 
                mu.owner LIKE "%' . $search . '%" ||
                mp.name LIKE "%' . $search . '%"
        ');

        if ($data) {
            return response()->json($data);
        } else {
            return response()->json(["message" => "UMKM not found."], 400);
        }
    }

    public function store(Request $request)
    {
        $photo = [];
        foreach ($request->photo as $key => $item) {
            $extension  = $item->getClientOriginalExtension(); //This is to get the extension of the image file just uploaded
            $name = preg_replace('/\s*/', '', $request->input('name'));
            $name = strtolower($name);
            $image_name = time() . '_' . $name . '_umkm.' . $extension;
            $bucket = config('filesystems.disks.s3.bucket');

            $path = Storage::disk('s3')->putFileAs('images', $item, $image_name, 'public');

            $link_image = 'https://link.storjshare.io/raw/jvvig5smuxvk53ifsajgprxkq3ua' . '/' . $bucket . '/images/' . $image_name;
            $photo[] = $link_image;
        }

        $id = $this->GenerateUniqIDUmkm($request->name);
        $data = [
            'id' => $id,
            'name' => $request->name,
            'desc' => $request->desc,
            'address' => $request->address,
            'city' => $request->city,
            'province' => $request->province,
            'long' => $request->long,
            'lat' => $request->lat,
            'owner' => $request->owner,
            'contact' => $request->contact,
            'photo' => json_encode($photo, JSON_UNESCAPED_SLASHES),
        ];
        DB::table('md_umkm')->insert($data);
        // var_dump(json_encode($request->products));

        foreach ($request->products as $key => $item) {

            $photo_product = [];
            foreach ($item['photo'] as $key => $photo_item) {
                $extension  = $photo_item->getClientOriginalExtension(); //This is to get the extension of the image file just uploaded
                $name = preg_replace('/\s*/', '', $item['name']);
                $name = strtolower($name);
                $image_name = time() . '_' . $name . '_product.' . $extension;
                $bucket = config('filesystems.disks.s3.bucket');

                $path = Storage::disk('s3')->putFileAs('images', $photo_item, $image_name, 'public');

                $link_image = 'https://link.storjshare.io/raw/jvvig5smuxvk53ifsajgprxkq3ua' . '/' . $bucket . '/images/' . $image_name;
                $photo_product[] = $link_image;
            }

            $data_product = [
                'id' => $this->GenerateUniqIDProduct($request->name),
                'id_umkm' => $id,
                'name' => $item['name'],
                'price' => $item['price'],
                'photo' => json_encode($photo_product, JSON_UNESCAPED_SLASHES),
            ];
            DB::table('md_product')->insert($data_product);
        }


        return response()->json(["message" => "UMKM added."], 200);
    }


    public function update(Request $request, $id)
    {
        $umkm = DB::selectOne('
            SELECT 
                mu.*
            FROM 
                md_umkm mu
            WHERE
                mu.id = "' . $id . '"
        ');

        if ($umkm) {
            $photo = [];
            foreach ($request->photo as $key => $item) {

                if ($request->hasFile('photo')) {
                    $extension  = $item->getClientOriginalExtension(); //This is to get the extension of the image file just uploaded

                    $name = preg_replace('/\s*/', '', $request->input('name'));
                    $name = strtolower($name);
                    $image_name = time() . '_' . $name . '_umkm.' . $extension;
                    $bucket = config('filesystems.disks.s3.bucket');

                    $path = Storage::disk('s3')->putFileAs('images', $item, $image_name, 'public');

                    $link_image = 'https://link.storjshare.io/raw/jvvig5smuxvk53ifsajgprxkq3ua' . '/' . $bucket . '/images/' . $image_name;
                    $photo[] = $link_image;
                } else {
                    $photo[] = $item;
                }
            }
            $data = [
                'name' => $request->name,
                'desc' => $request->desc,
                'address' => $request->address,
                'city' => $request->city,
                'province' => $request->province,
                'long' => $request->long,
                'lat' => $request->lat,
                'owner' => $request->owner,
                'contact' => $request->contact,
                'photo' => json_encode($photo, JSON_UNESCAPED_SLASHES),
            ];
            DB::table('md_umkm')->where(['id' => $id])->update($data);
            DB::table('md_product')->where(['id_umkm' => $id])->delete();

            foreach ($request->products as $key => $item) {

                $photo_product = [];
                foreach ($item['photo'] as $key => $photo_item) {
                    if (is_string($photo_item)) {
                        $photo_product[] = $photo_item;
                    } else {
                        $extension  = $photo_item->getClientOriginalExtension(); //This is to get the extension of the image file just uploaded
                        $name = preg_replace('/\s*/', '', $item['name']);
                        $name = strtolower($name);
                        $image_name = time() . '_' . $name . '_product.' . $extension;
                        $bucket = config('filesystems.disks.s3.bucket');

                        $path = Storage::disk('s3')->putFileAs('images', $photo_item, $image_name, 'public');

                        $link_image = 'https://link.storjshare.io/raw/jvvig5smuxvk53ifsajgprxkq3ua' . '/' . $bucket . '/images/' . $image_name;
                        $photo_product[] = $link_image;
                    }
                }
                $data_product = [
                    'id' => $this->GenerateUniqIDProduct($request->name),
                    'id_umkm' => $id,
                    'name' => $item['name'],
                    'price' => $item['price'],
                    'photo' => json_encode($photo_product, JSON_UNESCAPED_SLASHES),
                ];
                DB::table('md_product')->insert($data_product);
            }
            return response()->json(["message" => "UMKM updated."], 200);
        } else {
            return response()->json(["message" => "UMKM not found."], 400);
        }
    }

    public function delete($id)
    {
        $umkm = DB::selectOne('
            SELECT 
                mu.*
            FROM 
                md_umkm mu
            WHERE
                mu.id = "' . $id . '"
        ');

        if ($umkm) {
            DB::table('md_umkm')->where(['id' => $id])->delete();
            DB::table('md_product')->where(['id_umkm' => $id])->delete();
            return response()->json(["message" => "UMKM deleted."], 200);
        } else {
            return response()->json(["message" => "UMKM not found."], 400);
        }
    }

    public function GenerateUniqIDUmkm($var)
    {
        $string = preg_replace('/[^a-z]/i', '', $var);
        $vocal  = array("a", "e", "i", "o", "u", "A", "E", "I", "O", "U", " ");
        $scrap  = str_replace($vocal, "", $string);
        $begin  = substr($scrap, 0, 4);
        $uniqid = strtoupper($begin);
        return "UMKM_" . $uniqid . substr(md5(time()), 0, 3);
    }
    public function GenerateUniqIDProduct($var)
    {
        $string = preg_replace('/[^a-z]/i', '', $var);
        $vocal  = array("a", "e", "i", "o", "u", "A", "E", "I", "O", "U", " ");
        $scrap  = str_replace($vocal, "", $string);
        $begin  = substr($scrap, 0, 4);
        $uniqid = strtoupper($begin);
        return "PRODUCT_" . $uniqid . substr(md5(time()), 0, 3);
    }
}
