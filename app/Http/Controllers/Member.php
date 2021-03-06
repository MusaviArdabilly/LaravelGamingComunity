<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Exports\MembersExport;
use App\Members;

class Member extends Controller
{

    // public function index(){

    // public function landingPage(){
    //     $table = 'members';
    //     $showmember = Members::all();
    //     return view('index', compact('showmember'));
    // }

    public function index(Request $request){
      $showmember = Members::when($request->searchInput, function ($query) use ($request) {
                $query->where('name', 'LIKE', "%{$request->searchInput}%")
                      ->orWhere('code', 'LIKE', "%{$request->searchInput}%")
                      ->orWhere('rank', 'LIKE', "%{$request->searchInput}%");
                })->paginate(25);
      return view('members')->with('member', ($showmember));
   }
    
                    // public function index(){

        // $table = 'users';
    	// $showmember = Users::all();
     //    $showmember = Users::paginate(15);
                        // $table = 'members';
                        // $showmember = Members::all();
                        // $showmember = Members::paginate(15);
        // $showmember = Users::where('id', Auth:: id())->paginate(5);
    	               // return view('members')->with('member', $showmember);

    	// return view ('members', compact('table', 'fillable'));

        // $members = DB::table('users')->get();
        // // dump($members);
        // return view('members', ['users' => $members]);
    // }

    // public function addMemberForm(){
    //     return view('addMemberForm');
    // }

    public function addMember(Request $request){
        // dd($request->all());
        $this->validate($request,[
            'name' => 'required',
            'code' => 'required',
            'age' => 'required',
            'gender' => 'required',
            'rank' => 'required',
            'language' => 'required',
            'additional_info' => 'required',
            'avatarinput' => 'required|image|mimes:jpeg,png,jpg|max:2048']);
        $ava = $request->file('avatarinput');
        $extension = $ava->getClientOriginalExtension();
        Storage::disk('public')->put($ava->getFilename().'.'.$extension,  File::get($ava));
        Members::create([
            'name' => $request->name,
            'code' => $request->code,
            'age' => $request->age,
            'gender' => $request->gender,
            'rank' => $request->rank,
            'language' => $request->language,
            'additional_info' => $request->additional_info,
            'avatar' => $ava->getFilename().'.'.$extension
        ]);

        return redirect('/members');
    }

    // public function editMemberForm($id){
    //     $user = Members::find($id);
    //     return view('editMemberForm', ['user' => $user]);
    // }

    public function editMember($id, Request $request){
        // dd($request->all());
        // dd($path);
        $this->validate($request,[
           'name' => 'required',
           'code' => 'required',
           'age' => 'required',
           'gender' => 'required',
           'rank' => 'required',
           'language' => 'required',
           'additional_info' => 'required',
           'avatarinput' => 'nullable|image|mimes:jpeg,png,jpg|max:2048']);

        $user = Members::find($id);
        if ($ava = $request->file('avatarinput')){
            $uploaded_ava = public_path("uploads\{$user->avatar}"); // get image from folder

            if (File::exists($uploaded_ava)) { // remove image from folder
                unlink($uploaded_ava);
            }
            $destinationPath = 'uploads/'; // upload path
            $user_avatar = date('YmdHis') . "." . $ava->getClientOriginalExtension();
            $ava->move($destinationPath, $user_avatar);
            $insert['avatar'] = "$user_avatar";

            $user->name = $request->name;
            $user->code = $request->code;
            $user->age = $request->age;
            $user->gender = $request->gender;
            $user->rank = $request->rank;
            $user->language = $request->language;
            $user->additional_info = $request->additional_info;
            $user->avatar = $insert['avatar'] = "$user_avatar";
        }
            $user->save();
        return redirect('/members');
    }

    public function deleteMember($id)
    {   
        $deleteavatar = Members::where('id',$id)->first();
        File::delete('uploads/'.$deleteavatar->avatar);

        $user = Members::find($id);
        $user->delete();
        return redirect('/members');
    }

   //  public function search(Request $request){
   //    $search = Members::when($request->searchInput, function ($query) use ($request) {
   //              $query->where('name', 'LIKE', "%{$request->searchInput}%")
   //                    ->orWhere('code', 'LIKE', "%{$request->searchInput}%")
   //                    ->orWhere('rank', 'LIKE', "%{$request->searchInput}%");
   //              })->paginate(5);
   //    return view('/members')->with('member', ($search));
   // }

    public function export(){
        return Excel::download(new MembersExport, 'Members.xlsx');
    }
}