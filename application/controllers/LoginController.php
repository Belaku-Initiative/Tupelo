<?php
require 'globaldefs.php';
require 'lcfunctions.php';


/************************************************************
Controller Name:  Login
Actions        :  Index,authUser,signUp,addUser,logout
                  resetPwd,resetPwdEmail,userEmailValidation
Description    :  Has the above actions in it.  Control's the
                  entire user login,signin,email validation,
				  password reset functions             
*************************************************************/	
		
class LoginController extends Zend_Controller_Action
{
	protected $session;
	
    public function init()
    {
		$this->session=new Zend_Session_Namespace("login_session");
    }
/*----------------------------------------------------------------------------------
Action 	    		: Index
Description			: Displays main page of Travel APP.With option to login,signup,Fb login,
					  reset password.Starts a session if user users Fb login
Login Submit Action : AuthUser
------------------------------------------------------------------------------------*/
    public function indexAction()
    {
	
		if(!isset($this->session->name)) {
		
    		print_scr (D,"(indexAction)session is not set");
			
			//Refer Additional check added in fblogin action
			if(isset($this->session->errorfbLoginIndex )){
				$this->view->errorMessage = $this->session->errorfbLoginIndex;
				$this->session->errorfbLoginIndex=null;
			}
			//If regular users authentication has failed we display errors 
			//Note session->name is set only after authentication either fb or regular
			if(isset($this->session->errorAuthuserIndex )){
				$this->view->errorMessage = $this->session->errorAuthuserIndex;
				$this->session->errorAuthuserIndex=null;
			}
			//Create a new login form
			$this->view->form = getForm("login");
			if(isset($this->session->retainEmail)){
				$data = array( 'email' => $this->session->retainEmail);
				$this->view->form->populate($data);
			}
			//Login form action is set to authuser to validate the credentials
			//$user_profile=$this->session->userProfile;  
			$this->view->form->setAction($this->view->url(array('controller' => 'Login', 'action' => 'authuser'), 'default', TRUE));
    		
    	} else {
			print_scr(D,"(indexAction)session is set");
    		if (isset($this->session->isFBUser)) {
			        //Fb User has logged in
					$user_profile=$this->session->userProfile;  
					$this->view->userFirstName = $user_profile['first_name'];
					$this->view->userLastName= $user_profile['last_name'];
					$this->view->fbUser = 1;
					print_scr(D,"FB user.Session:".$this->session->name); 
			} else {
					// regular user who has logged in
					$this->view->regularUser=1;
					$this->view->userFirstName=$this->session->userFirstName;
					$this->view->userLastName=$this->session->userLastName;
			}
    	}	 
    }
/*------------------------------------------------------------------------
Action 	   : fblogin
Description: Retrieving the FB user details populate the DB, set the session variables, 
             redirect to user home page(travelapp vs main page depending on click)
--------------------------------------------------------------------------*/
	public function fbloginAction(){
	
		$facebook = getFB();
		$fbUser= $facebook->getUser();
		//Additional check to ensure user has loggedin to FB
		if ($fbUser) {
			$this->view->fbUser=$fbUser;
			//Set the session variables and populate the DB by calling adduser
			$user_profile = $facebook->api('/' . $fbUser);
			$userEMail = $user_profile['email'];
			// $userName = $user_profile['name'];
			// $userFirstName = $user_profile['first_name'];
			// $userLastName =  $user_profile['last_name'];
			// $userPic = "https://graph.facebook.com/".$fbUser."/picture";
			// $userId = $fbUser;
			// $userGender = $user_profile['gender'];
			
			print_scr(D,"FB User Email:".$userEMail);
			//exit;
			
			if(!isset($userEMail)){
				print_scr(E,"No Permission to retrieve the users email-id");
				//Should not come here. 
				//Reason: If user has logged in via FB he has to give access to email else he would not 
				// Pass the authentication and come this far
				return;
			}
			
			// $this->view->userName=$userName;
			// $this->view->userFirstName=$userFirstName;
			// $this->view->userLastName=$userLastName;
			// $this->view->userPic=$userPic;
			// $this->view->userId=$fbUser;
			// $this->view->userEMail=$userEMail;
			// $this->view->userGender=$userGender;
			
			//User profile is an array which hass all the user details, we can use this 
			//in the view
			$this->view->userProfile=$userProfile;
			
			//Setting session variables
			$this->session->name = $userEMail;
			$this->session->isFBUser = "true";
			$this->session->userProfile=$user_profile;
			$this->_redirect('/Login/adduser/'); //Populate the user profile
		} else {
		  print_scr (D,"(fbloginAction)Should not come here");
		  $this->session->errorfbLoginIndex = "Oops something went wrong, please re login";
		  $this->_redirect('/Login/');
		}	
	}
	
/*------------------------------------------------------------------------
Action 	   : AuthUser
Description: Authenticates the user using Zend Auth library
             Give option to resend email validator if user status
			 is not validated. If validated takes him into his home page.
--------------------------------------------------------------------------*/
	public function authuserAction()
	{
		//On Page refresh-if session is valid dont authenticate again
		if (isset($this->session->name))
		{
			$this->view->userFirstName=$this->session->userFirstName;
			$this->view->userLastName=$this->session->userLastName;
			$this->view->sendValidateEmail= $this->session->sendValidateEmail;
			return;
		}
		if (!$this->getRequest()->isPost()) {
			return $this->_forward('index');
		}
		$form = getForm("login");
		$form->isValid($_POST);
		
		$email = $form->getValue('email');
		$pwd = $form->getValue('password');
		
		$authadapter = getauthadapter($email);
		$authadapter->setIdentity($email)
					->setCredential($pwd);
		
		//For the actual authentication
		$auth = Zend_Auth:: getInstance();
		$result = $auth->authenticate($authadapter);
		
		if($result->isValid()) {
			// echo "authenticated";
			$identity = $authadapter->getResultRowObject();  //To get the entire details and store across app using zend storage
			$userIndex= $identity->userId;
			$row = fetchRow('tblMyProfile','userId',$userIndex);
			$userFirstName=$row->first_Name;
			$userLastName=$row->last_Name;
			$userEMail=$email;
			//Save Variable in the view
			$this->view->userFirstName=$userFirstName;
			$this->view->userLastName=$userLastName;
			$this->view->userEMail=$userEMail;
			
			$this->session->name = $userEMail;
			$this->session->userName = $userFirstName." ".$userLastName;
			$this->session->userFirstName=$userFirstName;
			$this->session->userLastName=$userLastName;
			
			// Check if user email is validated
			if($identity->user_Status!="validated") { //If user not yet validated, give him an option to resend the link
				$this->view->userIndex = $userIndex;
				$this->view->sendValidateEmail = true;
				$this->session->sendValidateEmail = true;
			} else {
				print_scr(D,"User: ".$userEMail." is validated");
				$this->view->regularUser=1;
				$this->session->regularUser = "true";
				print_scr(D,"Setting session variables Name:".$this->session->name);
			}	
		}
		else {
			// Need to throw approritae error and send to login page.
			//Also retaining the userId in the login form
			$this->session->errorAuthuserIndex = "The username or password you entered is incorrect";
			$this->session->retainEmail = $email ;
			$this->view->loginFailed=true;
			$this->_redirect('/login');
			
			print_scr(E,"User: ".$email." is invalid!!");
		}
	}
/*------------------------------------------------------------------------
Action 	           : SignUp
Description        : Displays user signup form. 
Form Submit Action : Adduser
--------------------------------------------------------------------------*/
    public function signupAction()
    {
    	//$this->session = new Zend_Session_Namespace("login_session");
    	if(isset($this->session->errAdduserSignup)){
			$this->view->errorMessage = $this->session->errAdduserSignup;
		}

		$this->session->addUser="true";
    	$form = new Application_Form_Signup();
    	$this->view->form = $form;
		if(isset($this->session->retainSignup)){ //Reatining the values upon signup page error
			$this->view->form->populate($this->session->retainSignup);
		}
    }
/*------------------------------------------------------------------------
Action 	           : AddUser
Description        : Add a user to DB, if FB user check if he/she exists
                     in DB else adds FB to DB.  Send an email to validate
					 user email
--------------------------------------------------------------------------*/
    public function adduserAction()
    {	
		//On Page refresh-if session is valid do nothing
		if (isset($this->session->name) && !isset($this->session->isFBUser))
		{
			$this->view->userFirstName=$this->session->userFirstName;
			$this->view->userLastName=$this->session->userLastName;
			return;
		}
	
		if (isset($this->session->isFBUser)){
	    	// If the action is called due to Facebook Login
			$user_profile=$this->session->userProfile;
			$userFirstName = $user_profile['first_name'];
			$userLastName =  $user_profile['last_name'];
			$userEMail = $user_profile['email'];
			$userGender = $user_profile['gender'];
			print_scr(D,"Fname:".$userFirstName.":::LName:".$userLastName.":::EMail:".$userEMail.":::Sex:".$userGender);
			
			$isEmailInDb = doesDbRecordExist('tblLogin','email',$userEMail);
			if (!$isEmailInDb){
				// First time facebok user has logged in, add details to DB
				$newUser = new Application_Model_DbTable_TblLogin();
				$newUser->loginTblInsert(array(
						'email'    => $userEMail, 
						'is_FB_User'    => "1", 
						'user_Status'    => "validated", 
				));
				$row = fetchRow('tblLogin','email',$userEMail);
				$userIndex= $row->userId;
				$userProfile = new Application_Model_DbTable_TblMyProfile();
				$userProfile->addNewProfile(array(
						'userId'           => $userIndex,
						'first_Name'       => $userFirstName,
						'last_Name'        => $userLastName,
						'gender'          => $userGender,
				));
				$this->_redirect('/login');
			} else { //If he is returning facebook user
				$this->_redirect('/login');
				print_scr (D,"User has account and logging using FB");
			}
			// else we have to redirect to user home page
	    	
		} else if (isset($this->session->addUser)) {
			
			//Check if username already exists, if not add,else redirect it to signup page
			$emailId = $_POST['email'];
			$isEmailAlreadyInDb = doesDbRecordExist('tblLogin','email',$emailId);
			$isFbUser = isFbUser($emailId);   //If we he is a FB user and now trying to sign up we should allow him
			if($isEmailAlreadyInDb && !$isFbUser)
			{
				$this->session->errAdduserSignup = "This email id is already taken, please enter another one";
				//Reatining the values entered by user upon error 
				$retainSignup = array(
					'firstName'       => $_POST['firstName'],
					'lastName'        => $_POST['lastName'],
					'gender'          => $_POST['gender'],
					'contact'         => $_POST['contact']   
				);
				$this->session->retainSignup = $retainSignup;
				$this->_redirect('login/signup');
			}
			elseif($isEmailAlreadyInDb && $isFbUser	) //Fb user trying to signup, so we need to overwrite/append the details to the same row.
			{
			       
					$userValidateTime = date('Y/m/d H:i:s'); //Used for user email validation
					$userValidateUuid = generateUuid();
					$salt = "@#$&^%!(*)"; 
					$password = sha1($salt. $_POST['email']. $_POST['password']);
					
					$row = fetchRow('tblLogin','email',$emailId);
					$userIndex= $row->userId; //Used to fetch the Myprofile Table
					$row->password 			 =	$password;
					$row->user_Validate_Uuid =	$userValidateUuid;
					$row->user_Validate_Time 	 =	$userValidateTime;
					$row->user_Status = "nonvalidated";  //Since by default FB user is validated, we change it back since we now want the user to validate his email
					$row->save();
					
					$row = fetchRow('tblMyProfile','userId',$userIndex);
					if(isset($_POST['firstName'])) //Making sure we do not overwrite the entries with null
					{
						$row->first_Name	=  $_POST['firstName'];
					}
					if(isset($_POST['lastName'])) //Making sure we do not overwrite the entries with null
					{
						$row->last_Name	=  $_POST['firstName'];
					}
					if(isset($_POST['gender'])) //Making sure we do not overwrite the entries with null
					{
						$row->gender	= $_POST['gender'];
						echo $_POST['gender'];
						exit;
					}
					if(isset($_POST['contact'])) //Making sure we do not overwrite the entries with null
					{
						$row->contact	=  $_POST['contact'];
					}
					$row->save();
			}
			else
			{
				//Else add the user
				$userValidateTime = date('Y/m/d H:i:s'); //Used for user email validation
				$userValidateUuid = generateUuid();
				
				$salt = "@#$&^%!(*)"; 
				$newUser = new Application_Model_DbTable_TblLogin();
				$newUser->loginTblInsert(array(
						'email'    => $_POST['email'],
						'password' => sha1($salt. $_POST['email']. $_POST['password']),
						'user_Validate_Uuid' => $userValidateUuid,
						'user_Validate_Time' => $userValidateTime
				));

				//Need to send out an email to the user for validation, email link has UUID and user index in tblLogin
				$row = fetchRow('tblLogin','email',$emailId);
				$userIndex= $row->userId;
				
				$userProfile = new Application_Model_DbTable_TblMyProfile();
				$userProfile->addNewProfile(array(
						'userId'           => $userIndex,
						'first_Name'       => $_POST['firstName'],
						'last_Name'        => $_POST['lastName'],
						'gender'          => $_POST['gender'],
						'contact'         => $_POST['contact']  

				));
			}
			//Common code for both elseif and else cases.  Setting the session and sending an email to validate		
			$this->session->name=$emailId;
			$this->session->userFirstName = $_POST['firstName'];
			$this->session->userLastName = $_POST['lastName'];
			
			$emailVerificationLink = "www.stubees.com/".getModule()."/public/Login/useremailvalidation?uuid=".$userValidateUuid."&pid=".$userIndex;
			
			$htmlToSend = "<h5>Hello ".$_POST['firstName']." Welcome to Stubees!!</h5><br />Please verify your account by clicking here:<br />".$emailVerificationLink;
			$fromEmail = "noreply@stubees.com";  //Make it global Variable
			$fromName = "Stubees";
				
			sendEmail($fromEmail, $fromName,$emailId,$_POST['firstName'],"Verification",$htmlToSend,0);
    	}
		else
		{
    		$this->_redirect('/login/index/');
		}
    }
/*------------------------------------------------------------------------
Action 	           : Logout
Description        : Clears the session, takes to stubees.com. 
--------------------------------------------------------------------------*/
    public function logoutAction()
    {
		$authAdapter = Zend_Auth::getInstance();
        $authAdapter->clearIdentity();
		$this->session->unsetAll();
		Zend_Session::destroy( true );
		$this->_redirect('/index');
    }
/*--------------------------------------------------------------------------
Action 	           : ResetPwd
Description        : Inovked when users clicks on reset password. 
                     Ensure Email is valid, generates a link and sends email
----------------------------------------------------------------------------*/
    public function resetpwdAction()
    {
        //If the email link is expired we get an error message 
		
    	if(isset($this->session->errResetpwdemailResetpwd))
		{
			$this->view->errorMessage = $this->session->errResetpwdemailResetpwd;
		}
    	$sendEmailFlag = 0;
    	$form = new Application_Form_Userid();
    	$this->view->form = $form;
    	if ($this->getRequest()->isPost())
    	{
    		$form = getForm("login");
    		$form->isValid($_POST);
    		$email = $form->getValue('email');
    		
    		// Making sure the email to send the reset link is valid
    		$validEmail = validateEmail($email);
    		if ($validEmail) 
    		{// email appears to be valid
    			
    		    $isEmailInDb = doesDbRecordExist('tblLogin','email',$email); //Check that the email address exists in the database
    			if($isEmailInDb){
    				$row = fetchRow('tblLogin','email',$email);
					$ifFbUser = $row->is_FB_User; //If he is a FB user no option to reset passwrod
					$this->view->ifFbUser = $ifFbUser; //Should not display form again
					if($ifFbUser){
						$this->view->errorMessage = "Oops! It looks like you logged in with Facebook, please visit fb.com to reset your password";
					}
					else{
						$this->view->emailFoundFlag=1;// email found in db
						$sendEmailFlag = 1;
						$userIndex = $row->userId;	
					}						
    			}
    			else{
    				// email address does not exists; print the reasons
    				$this->view->errorMessage = "Email Id does not exists, please enter email used at the time of sign up";  
    			}
    		} 
    		else {
    			$this->view->errorMessage = "Please enter a valid email";
    			
    		}	
    	}
    	if($sendEmailFlag && (!isset($this->session->resetPwdEmailSent)))  //To avoid Dos attack by refreshing page to send coninous email's in same session
    	{
    		//Need To send A email & add an entry to the table for corresponding email
    		$date = date('Y/m/d H:i:s');
    		$uuid = generateUuid();
    		$row = fetchRow('tblLogin','email',$_POST['email']);
    		if(count($row)!= 0)
    		{
    			//updateRow('tblLogin',$row,$columnName,$newValue); //
    			$row->reset_Pwd_Uuid = $uuid;
    			$row->reset_Pwd_Time = $date;
    			$row->save();
				
				//The email link sent to user will have uuid and userindex to help in processing the link when he clicks
				$userEmail = $_POST['email'];
				$myProfileRow = fetchRow('tblMyProfile','userId',$userIndex); //Retrieve the first name of the user
				$toName =  $myProfileRow->first_Name;
				$resetPwdLink = "www.stubees.com/".getModule()."/public/Login/resetpwdemail?uuid=".$uuid."&pid=".$userIndex;
				$htmlToSend = "<h5>Hello ".$toName."</h5><br />Reset your password by clicking here:<br />".$resetPwdLink;
				$fromEmail = "noreply@stubees.com";  //Make it global Variable
				$fromName = "Stubees";		
				sendEmail($fromEmail, $fromName,$userEmail,$toName,"Reset Password Request",$htmlToSend,0);
				$this->session->resetPwdEmailSent = "true";
    		}
    		else
    		{
    			echo "invalid condition\n";
    		}
    			
    	}
    }
/*------------------------------------------------------------------------------------------
Action 	           : ResetPwdEmail
Description        : Inovked when users clicks on the email link he receives
                     Checks if link is valid and not expired. Submit a form to resetpassword
Form Action		   : Same action, replaces the password with new password
---------------------------------------------------------------------------------------------*/
    public function resetpwdemailAction()
    {
	    
        if (!$this->getRequest()->isPost()) // If its not a post action, then its user cliking the email link to reset the Pwd
        {
        	$oneday = 60*60*24;
        	$uuid = $_GET['uuid']; // UniqueId sent via email
        	$userIndex = $_GET['pid']; //UserIndex
        	$rows = dbTblFind('tblLogin',$userIndex);
        	if($rows->count()!= 1) //Check if the user exits 
        	{
        		//need to redirect to reset password with the error msg
        		//$this->_helper->FlashMessenger->addMessage("inavlid Link, please enter the user id again", 'actions');
				$this->session->errResetpwdemailResetpwd = "inavlid Link, please enter the user id again";
        		$this->_redirect('login/resetpwd');
        	}
        	elseif($rows->count()== 1)// We should find only one row
        	{
        		$row = $rows->current();
        		//Check if uuid macthes the one saved in the DB
        		$uuidInDb = $row->reset_Pwd_Uuid;
        		if($uuid != $uuidInDb)
        		{
        			//$this->_helper->FlashMessenger->addMessage("inavlid Link, please enter the user id again", 'actions');
					$this->session->errResetpwdemailResetpwd = "inavlid Link, please enter the user id again";
        			$this->_redirect('login/resetpwd');
        			
        		}
        		// Validate the Time
        		$timeInDb = $row->reset_Pwd_Time;
        		$timeInDb = strtotime($timeInDb);
        		if($timeInDb < (time()-$oneday))
        		{
        			//$this->_helper->FlashMessenger->addMessage("Link expried please enter email id", 'actions');
					$this->session->errResetpwdemailResetpwd = "Link expried please enter email id";
        			$this->_redirect('login/resetpwd');
        		}
        		else
        		{
        			//Give prompt to reset pwd, update db with new pwd,
        			//nullify corresponding resetpeduuidentry from login tbl table(only after resets the pwd)
				//Since its a valid link,if the user has not been validated yet chaning the status to validated
					$status = $row->user_Status;
					if($status == "nonvalidated") 
					{
						$row->user_Status = "validated";
						$row->save();
					}
					$this->session->resetUserEmail= $row->email; //Used in reseting the password for this user
        			$form = new Application_Form_Resetpwd();//Application_Form_Resetpwd
        			$this->view->form = $form;
        			$this->view->form->setAction($this->view->url(array('controller' => 'login', 'action' => 'resetpwdemail'), 'default', TRUE));
        		}
        	}
        }
        
        if ($this->getRequest()->isPost()) //If its a post action, then its user submitting the pwd 
        {
        	//Have to ensure only one unique id exists in the table per user, i.e say user 
        	//chooses to reset the password twice within 24 hours only the lastest link shd be valid
        	//So delete the previous uuid before adding the new one
        	
        	//Reset the password in the log in tbl
        	$salt =  "@#$&^%!(*)"; //Global Variables??
        	$userEmail = $this->session->resetUserEmail;//$_POST['email'];
        	// Reset The Password
        	$row = fetchRow('tblLogin','email',$userEmail);
        	$newPwd = sha1($salt. $userEmail. $_POST['password']);
        	if(count($row)!= 0)
        	{
        	  $row->password = $newPwd;  //Save the new pwd
        	  $row->reset_Pwd_Uuid = null; //Nullify the uuid and time entries
        	  $row->reset_Pwd_Time = null;
        	  $row->save();
			  $this->view->succesFlag = 1;
        	}
        	else 
        	{
        		echo "invalid email id: Should not hit this condition";
        	}
        	 
        }
    }
/*------------------------------------------------------------------------------------------
Action 	           : UserEmailValidation
Description        : Inovked when users clicks on the email link he receives to validate 
					 email.  Checks if link is valid and not expired. If valid change the status of 
					 user in DB. If inavalid give an option to regenarate email
---------------------------------------------------------------------------------------------*/
    public function useremailvalidationAction()
    {
	    
		
    	if (!$this->getRequest()->isPost()) // If its not a post action, then its user cliking the email link to validate the email
    	{
    		$twoDays = 2*60*60*24;
    		$uuid = $_GET['uuid']; // UniqueId sent via email
    		$userIndex = $_GET['pid']; //UserIndex
    		if($uuid == "aaa") //User Requesting to send a mail, Need to send a mail and reset the entries for his row
    		{
    			$row = fetchRow('tblLogin','userId',$userIndex);
    			$userValidateTime = date('Y/m/d H:i:s'); //Generating new time
    			$userValidateUuid = generateUuid();  // Genertating new uuid
    			$row->user_Validate_Time = $userValidateTime; //Saving new entries into the table
    			$row->user_Validate_Uuid = $userValidateUuid;
    			$row->save(); //Saved the new entries
    			//Send Email
			$emailId = $row->email;
			$myProfileRow = fetchRow('tblMyProfile','userId',$userIndex); //Retrieve the first name of the user
			$toName =  $myProfileRow->first_Name;
				
			$emailVerificationLink = "www.stubees.com/".getModule()."/public/Login/useremailvalidation?uuid=".$userValidateUuid."&pid=".$userIndex;
		        $htmlToSend = "<h5>Hello ".$toName." Welcome to Stubees!!</h5><br />Please verify your account by clicking here:<br />".$emailVerificationLink;
			$fromEmail = "noreply@stubees.com";  //Make it global Variable
			$fromName = "Stubees";
			sendEmail($fromEmail, $fromName,$emailId,$toName,"Verification",$htmlToSend,0);
    			$this->view->sendValidateEmail = true;
    			return;
    			
    		}
    	    // User has clicked the email link for validation.
    	    //Check if its a valid link(time, uuid)
    		$rows = dbTblFind('tblLogin',$userIndex);
			$row = $rows->current();
    		if($rows->count()!= 1) //Check if the user exits
    		{
    			
    			$this->view->errorMessage ="Inavlid Link, please enter the user id again we will send you a mail";
    			$form = new Application_Form_Userid();
    			$this->view->form = $form;
    			$this->view->form->setAction($this->view->url(array('controller' => 'login', 'action' => 'useremailvalidation'), 'default', TRUE));
    			//return;
    		}
    		else
    		{
    			//Check if uuid macthes the one saved in the DB
    			$uuidInDb = $row->user_Validate_Uuid;
    			if($uuid != $uuidInDb)
    			{
    				$this->view->errorMessage ="Inavlid Link, please enter the user id again we will send you a mail";
    				$form = new Application_Form_Userid();
    				$this->view->form = $form;
    				$this->view->form->setAction($this->view->url(array('controller' => 'login', 'action' => 'useremailvalidation'), 'default', TRUE));
    				return;
    			}
				if(!($row->user_Status == "validated"))
				{
				  // Validate the Time
					$timeInDb = $row->user_Validate_Time;
					$timeInDb = strtotime($timeInDb);
					if($timeInDb < (time()-$twoDays))
					{
						$this->view->errorMessage ="Link Expired, enter email again we will send you a new link";
						$form = new Application_Form_Userid();
						$this->view->form = $form;
						$this->view->form->setAction($this->view->url(array('controller' => 'login', 'action' => 'useremailvalidation'), 'default', TRUE));
					}
					else
					{
						//User Validated change the staus in DB
						$row->user_Status = "validated";  //Save the new pwd
						$row->user_Validate_Time = null; //Nullify the uuid and time entries
						$row->user_Validate_Uuid = null;
						$row->save();	
						//echo "User Account Validated";
						$this->view->isValidated =1;
					}
				}
				else  //User Exists and status is validated
				{
				   //echo "User Account Validated Already";
				   $this->view->goHomeFlag = 1;
				}
    			
    		}
			
    	}
    	if ($this->getRequest()->isPost()) //If its a post action, then its user is manually entering his id to generate a link to validate email
    	{ //Because his link is expired or invalid
    		
    		$userEmail = $_POST['email'];
    		$row = fetchRow('tblLogin','email',$userEmail);
			if(!$row)
			{
				$this->view->errorMessage = "Please enter the email id you used during signup";
				$form = new Application_Form_Userid();
				$this->view->form = $form;
				$this->view->form->setAction($this->view->url(array('controller' => 'login', 'action' => 'useremailvalidation'), 'default', TRUE));
				return;
			}
    		$userIndex = $row->userId;
    		$userValidateTime = date('Y/m/d H:i:s'); //Generating new time
    		$userValidateUuid = generateUuid();  // Genertating new uuid
    		$row->user_Validate_Time = $userValidateTime; //Saving new entries into the table
    		$row->user_Validate_Uuid = $userValidateUuid;
    		$row->save(); //Saved the new entries
    		//Send Email
		$myProfileRow = fetchRow('tblMyProfile','userId',$userIndex); //Retrieve the first name of the user
		$toName =  $myProfileRow->first_Name;
		$emailVerificationLink = "www.stubees.com/".getModule()."/public/Login/useremailvalidation?uuid=".$userValidateUuid."&pid=".$userIndex;
		$htmlToSend = "<h5>Hello ".$toName." Welcome to Stubees!!</h5><br />Please verify your account by clicking here:<br />".$emailVerificationLink;
		$fromEmail = "noreply@stubees.com";  //Make it global Variable
		$fromName = "Stubees";		
		sendEmail($fromEmail, $fromName,$userEmail,$toName,"Verification",$htmlToSend,0);
    		$this->view->sendValidateEmail = true;
    		return;

    	}
    }
	//TEST METHOD
	public function testingAction(){
	
	echo getModule();
	}
}


 




