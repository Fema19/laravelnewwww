<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class KaryawanApiController extends Controller
{
    public function index()
    {
        return response()->json(Karyawan::with('departemen')->get());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nip' => 'required|unique:karyawan,nip',
            'nama_karyawan' => 'required',
            'gaji_karyawan' => 'required|numeric',
            'alamat' => 'required',
            'jenis_kelamin' => 'required|in:pria,wanita',
            'departemen_id' => 'required|exists:departemen,id',
            'foto' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $request->except('foto');

        if ($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $nama_foto = date('YmdHis') . '.' . $foto->getClientOriginalExtension();
            $foto->move(public_path('foto'), $nama_foto);
            $data['foto'] = $nama_foto;
        }

        $karyawan = Karyawan::create($data);
        return response()->json($karyawan, 201);
    }

    public function show($id)
    {
        $karyawan = Karyawan::with('departemen')->find($id);
        if (!$karyawan) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }
        return response()->json($karyawan);
    }

    public function update(Request $request, $id)
    {
        $karyawan = Karyawan::find($id);
        if (!$karyawan) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_karyawan' => 'sometimes|required',
            'gaji_karyawan' => 'sometimes|required|numeric',
            'alamat' => 'sometimes|required',
            'jenis_kelamin' => 'sometimes|required|in:pria,wanita',
            'departemen_id' => 'sometimes|required|exists:departemen,id',
            'foto' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $request->except('foto');

        if ($request->hasFile('foto')) {
            if ($karyawan->foto && File::exists(public_path('foto/' . $karyawan->foto))) {
                File::delete(public_path('foto/' . $karyawan->foto));
            }
            $foto = $request->file('foto');
            $nama_foto = date('YmdHis') . '.' . $foto->getClientOriginalExtension();
            $foto->move(public_path('foto'), $nama_foto);
            $data['foto'] = $nama_foto;
        }

        $karyawan->update($data);
        return response()->json($karyawan);
    }

    public function destroy($id)
    {
        $karyawan = Karyawan::find($id);
        if (!$karyawan) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        if ($karyawan->foto && File::exists(public_path('foto/' . $karyawan->foto))) {
            File::delete(public_path('foto/' . $karyawan->foto));
        }

        $karyawan->delete();
        return response()->json(['message' => 'Data berhasil dihapus'], 204);
    }
}
