<?php

namespace App\Http\Controllers;

use App\Mail\InviteLink;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use ImageResize;

class ApiController extends Controller
{

    /**
     * Generate Invitation Link For the User By Admin.
     *
     * @return \Illuminate\Http\Response
     */
    public function invitationLink($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = "Invalid email format";
            return response()->json(['error' => $emailErr], 404);
        }
        
        $details = [
            'title' => 'Invitation link for registration',
            'body' => 'This is for invitation link for the registration you can register by clicking this link',
            'link' => route('register')
        ];
        
        Mail::to($email)->send(new InviteLink($details));
       
        return response()->json(['email' => 'invitation link is sent'], 200);
    }

    /**
     * Registration
     */
    public function register(Request $request)
    {
        // dd($request);
        $this->validate($request, [
            'name' => 'required|min:4',
            'email' => 'required|email',
            'password' => 'required|min:8',
            'user_name' => 'required|min:6',
            'user_role' => 'required'
        ]);

        if($request->file()) {

            $fileName = time().'_'.$request->avatar->getClientOriginalName();
            $filePath = $request->file('avatar')->storeAs('uploads/avatar', $fileName, 'public');
            $avatar = '/storage/' . $filePath;
            
            $img = ImageResize::make(public_path() . $avatar);
            $width = 256;
            $height = 256;
            $img->fit($width, $height);
            $img->save(public_path() . $avatar);

        }

        $characters = '0123456789';
        $charactersLength = strlen($characters);
        $code = '';
        for ($i = 0; $i < 6; $i++) {
            $code .= $characters[rand(0, $charactersLength - 1)];
        }
        // dd($code);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'user_name' => $request->user_name,
            'pincode' => (int)$code,
            'user_role' => $request->user_role,
            'avatar' => $avatar ?? ''
        ]);

        $token = $user->createToken('LaravelAuthApp')->accessToken;

        $user->sendEmailVerificationNotification();
 
        return response()->json(['token' => $token], 200);
    }
 
    /**
     * Login
     */
    public function login(Request $request)
    {
        $data = [
            'email' => $request->email,
            'password' => $request->password
        ];
 
        if (auth()->attempt($data)) {
            $token = auth()->user()->createToken('LaravelAuthApp')->accessToken;
            return response()->json(['token' => $token], 200);
        } else {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    }  

    public function ProfileDetail($id){
        $user = User::findOrFail($id);
        $data = [
            'name' => $user->name,
            'username' => $user->user_name,
            'email' => $user->email,
            'user_role' => $user->user_role == 1 ? 'admin' : 'user',
            'avatar' => config('app.url') . $user->avatar
        ];
        return response()->json(['data' => $data], 200);
    }

    

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
