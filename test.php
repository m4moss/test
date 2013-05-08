<?php

class Home_Controller extends Base_Controller {

  /*
	|--------------------------------------------------------------------------
	| The Default Controller
	|--------------------------------------------------------------------------
	|
	| Instead of using RESTful routes and anonymous functions, you might wish
	| to use controllers to organize your application API. You'll love them.
	|
	| This controller responds to URIs beginning with "home", and it also
	| serves as the default controller for the application, meaning it
	| handles requests to the root of the application.
	|
	| You can respond to GET requests to "/home/profile" like so:
	|
	|		public function action_profile()
	|		{
	|			return "This is your profile!";
	|		}
	|
	| Any extra segments are passed to the method as parameters:
	|
	|		public function action_profile($id)
	|		{
	|			return "This is the profile for user {$id}.";
	|		}
	|
	*/

	public function action_index()
	{
		return View::make('home.index');
	}

	public function action_register_submit()
	{
		$formData = Input::only(
						array(
							 'name',
							 'firstname',
							 'sex',
							 'username',
							 'password2'
							 )
							);
		$role_id = Input::get('role');
		$formData['password2'] = Hash::make($formData['password2']);
		$subject_id =Input::get('subject');
		$class_arm = Input::get('arm');
		$schoolclass = Input::get('schoolclass');
		$formData['passport'] = '/img/'.Input::file('passport.name');
		$subject_teacher = User::create($formData);

		$subject_teacher->school_classes()->attach($schoolclass,
		array(
			'role_id'=>$role_id,
			'classarm_id'=> $class_arm,
			 'subject_id'=> $subject_id
			 )
		);
	
		//lets upload the passport
		$fileName = Input::file('passport.name');
		Input::upload(
			'passport',
			 path('public').'\img\passport' ,
			 $fileName
			 );
		return View::make('thanks');
	}

	public function action_login()
	{
		$username = Input::get('username');
		$password = Input::get('password');
		$credentials = array(
			'username' => $username,
			 'password' => $password
			 );
		$user = User::where('username','=', $username)->first();

		if (Auth::attempt($credentials)) {
			$user = Auth::user();
			if ($user->roles()->count()==1) {
			$role = $user->roles()->only('name');
			$dest = strtolower(str_replace(' ', '_', $role));
			return Redirect::to_route($dest);
			}else{
			return Redirect::to_route('choose_destination');
			}
		}else{
			return Redirect::to_route('teacher_register');
		}
	}


	//subject teacher methods.
	public function action_subject_teacher()
	{
		return View::make('subject_teacher')
			->nest('content', 'subject_teacher.home');
	}

	public function action_subjteacher_json()
	{

		$json = User::with(array('school_classes','class_arms','subjects'))
					->where('id','=', Auth::user()->id)
					->get();
		return json_encode($json);
	}

	//class teacher methods.
	public function action_class_teacher()
	{
		return View::make('class_teacher')
			->nest('content', 'class_teacher.home');
	}

	public function action_choose_destination()
		{		
			$user = Auth::user();
			$roles = $user->roles()->lists('name');
			$droles = array();
			$iroles = array();
			for ($x=0; $x <count($roles); $x++) {
				$iroles['name'] = $roles[$x];
				$iroles['link'] = strtolower(
					str_replace(' ', '_', $roles[$x]
						)
					);
				$droles[] = $iroles;
			}
		return View::make(
			'choose_destination',
			array('roles'=>$droles
				)
			);
	}

	public function action_student()
	{
		//code for student.
	}







}
