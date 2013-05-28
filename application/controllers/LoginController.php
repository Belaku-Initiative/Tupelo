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
    public function init()
    {
    }
/*----------------------------------------------------------------------------------
Action 	    		: Index
Description			: Displays main page of Travel APP.With option to login,signup,Fb login,
					  reset password.Starts a session if user users Fb login
Login Submit Action : AuthUser
------------------------------------------------------------------------------------*/
    public function indexAction()
    {	
    	// This Action is always invoked through AJAX : Disabling Layout
    	$layout = $this->_helper->layout();
    	$layout->disableLayout();

    	$session = new Zend_Session_Namespace("login_session");
		if(!isset($session->name)) {
    		print_scr (D,"(indexAction)session is not set");
			$facebook = getFB();
    		$user = $facebook->getUser();
    		$this->view->user=$user;
    		if ($user) {
    			$user_profile = $facebook->api('/' . $user);
    			$userName = $user_profile['name'];
    			$userFirstName = $user_profile['first_name'];
    			$userLastName =  $user_profile['last_name'];
    			$userPic = "https://graph.facebook.com/".$user."/picture";
    			$userId = $user;
    			$userEMail = $user_profile['email'];
    			$userGender = $user_profile['gender'];
    			
				if(!isset($userEMail))
				{
					print_scr(E,"No Permission to retrieve the users email-id");
					return;
				}
				
    			$this->view->userName=$userName;
    			$this->view->userFirstName=$userFirstName;
    			$this->view->userLastName=$userLastName;
    			$this->view->userPic=$userPic;
    			$this->view->userId=$user;
    			$this->view->userEMail=$userEMail;
    			$this->view->userGender=$userGender;
    			$session->name = $userEMail;
    			$session->fbUser = "true";
    			$session->userProfile=$user_profile;
    			$this->_redirect('/login/adduser/');
    		} else {
    			// handle regular user who has to login
    			$this->view->form = getForm();
    			$this->view->form->setAction($this->view->url(array('controller' => 'Login', 'action' => 'authuser'), 'default', TRUE));
    		}
    	} else {
			print_scr(D,"(indexAction)session is set");
    		if (isset($session->fbUser)) {
				$facebook = getFB();
				$user = $facebook->getUser();
				$this->view->user=$user;
				if ($user) {
					$user_profile = $facebook->api('/' . $user);
					$userName = $user_profile['name'];
					$userFirstName = $user_profile['first_name'];
					$userLastName =  $user_profile['last_name'];
					$userPic = "https://graph.facebook.com/".$user."/picture";
					$userId = $user;
					$userEMail = $user_profile['email'];
					$userGender = $user_profile['gender'];
				
					$this->view->userName=$userName;
					$this->view->userFirstName=$userFirstName;
					$this->view->userLastName=$userLastName;
					$this->view->userPic=$userPic;
					$this->view->userId=$user;
					$this->view->userEMail=$userEMail;
					$this->view->userGender=$userGender;
					
					print_scr(D,"FB user.Session:".$session->name);
					
				} else {
					print_scr(E,"FB User.Failure Case");
				}
			} else {
					// regular user who has logged in
					$session->name=$session->name;
					echo "regular user logged in<br />";
					echo "session name:".$session->name."<br />";
					//$this->render'loginform');
			}
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
	$session = new Zend_Session_Namespace("login_session");
	
	if (!$this->getRequest()->isPost()) {
		return $this->_forward('index');
	}
	$form = getForm();
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
		$this->view->userFirstName=$userFirstName;
		$this->view->userLastName=$userLastName;
		$this->view->userEMail=$userEMail;
		
		// Check if user email is validated
		if($identity->user_Status!="validated") { //If user not yet validated, give him an option to resend the link
			$this->view->userIndex = $userIndex;
			$this->view->sendValidateEmail = true;
		} else {
		
			print_scr(D,"User: ".$userEMail." is validated");
			
			$this->view->regularUser=1;

			
			$session->regularUser = "true";
			$session->name = $userEMail;
			
			print_scr(D,"Setting session variables Name:".$session->name);
		}	
	}
	else {
		// Need to throw approritae error and send to login page
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
    	$session = new Zend_Session_Namespace("login_session");
    	if(isset($session->errAdduserSignup))
			echo $session->errAdduserSignup;
		
		$session->addUser="true";
    	$form = new Application_Form_Signup();
    	$this->view->form = $form;
    }
/*------------------------------------------------------------------------
Action 	           : AddUser
Description        : Add a user to DB, if FB user check if he/she exists
                     in DB else adds FB to DB.  Send an email to validate
					 user email
--------------------------------------------------------------------------*/
    public function adduserAction()
    {
    	$session = new Zend_Session_Namespace("login_session");
		if (isset($session->fbUser)){
	    	// If the action is called due to Facebook Login
	    	if($session->fbUser=="true"){
	    		$user_profile=$session->userProfile;
	    		$userName = $user_profile['name'];
	    		$userFirstName = $user_profile['first_name'];
	    		$userLastName =  $user_profile['last_name'];
	    		$userEMail = $user_profile['email'];
	    		$userGender = $user_profile['gender'];
				
	    		print_scr(D,"UserName:".$userName.":::Fname:".$userFirstName.":::LName:".$userLastName.":::EMail:".$userEMail.":::Sex:".$userGender);
				
				$session->name=$userEMail;
	    		$isEmailInDb = doesDbRecordExist('tblLogin','email',$userEMail);
	    		if (!$isEmailInDb){ 
	    			// First time facebok user has logged in 
	    			$newUser = new Application_Model_DbTable_TblLogin();
	    			$newUser->loginTblInsert(array(
	    					'email'    => $userEMail 
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
	    		} else {
					$this->_redirect('/login');
					echo "User has account and logging using FB";
				}
	    		// else we have to redirect to user home page
	    	}
		} else if (isset($session->addUser)) {
			if($session->addUser=="true")
			{
				//Check if username already exists, if not add,else redirect it to signup page
				$emailId = $_POST['email'];
				$isEmailAlreadyInDb = doesDbRecordExist('tblLogin','email',$emailId);
				if($isEmailAlreadyInDb)
				{
					//$this->_helper->FlashMessenger->addMessage("User Id already exists, please enter another one", 'userNameUsed');
					$session->errAdduserSignup = "User Id already exists, please enter another one";
					$this->_redirect('login/signup');
				}
						
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
				$session->name=$emailId;
		    	
				$emailVerificationLink = "www.stubees.com/scooby/public/Login/useremailvalidation?uuid=".$userValidateUuid."&pid=".$userIndex;
				
				$htmlToSend = "<h5>Hello ".$_POST['firstName']." Welcome to Stubees!!</h5><br />Please verify your account by clicking here:<br />".$emailVerificationLink;
				$fromEmail = "noreply@stubees.com";  //Make it global Variable
				$fromName = "Stubees";
					
				sendEmail($fromEmail, $fromName,$emailId,$_POST['firstName'],"Verification",$htmlToSend,0);
				
				
			}
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
		Zend_Session::destroy( true );
    }
/*--------------------------------------------------------------------------
Action 	           : ResetPwd
Description        : Inovked when users clicks on reset password. 
                     Ensure Email is valid, generates a link and sends email
----------------------------------------------------------------------------*/
    public function resetpwdAction()
    {
        //If the email link is expired we get an error message 
    	//$messages = $this->_helper->FlashMessenger->getMessages('actions');
    	if(isset($session->errResetpwdemailResetpwd))
    	echo $session->errResetpwdemailResetpwd;
    	
    	$sendEmailFlag = 0;
    	$form = new Application_Form_Userid();
    	$this->view->form = $form;
    	
    	
    	if ($this->getRequest()->isPost())
    	{
    		$form = getForm();
    		$form->isValid($_POST);
    		$email = $form->getValue('email');
    		
    		// Making sure the email to send the reset link is valid
    		$validEmail = validateEmail($email);
    		if ($validEmail) 
    		{// email appears to be valid
    			
    		    $isEmailInDb = doesDbRecordExist('tblLogin','email',$email); //Check that the email address exists in the database
    			if($isEmailInDb)
    			{
    				$this->view->flag=1;// email found in db
    				$sendEmailFlag = 1;
    				$row = fetchRow('tblLogin','email',$email);
    				$userIndex = $row->userId;
    				//echo $userIndex;
    				 
    			}
    			else
    			{
    				// email address does not exists; print the reasons
    				$this->view->errormsg = "Email Id does not exists, please enter email used at the time of sign up";
    			    
    			}
    			
    		} 
    		else 
    		{
    			$this->view->errormsg = "Please enter a valid email";
    			
    		}
    		
    		
    	}
    	if($sendEmailFlag)
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
				$resetPwdLink = "www.stubees.com/scooby/public/Login/resetpwdemail?uuid=".$uuid."&pid=".$userIndex;
				$htmlToSend = "<h5>Hello ".$toName."</h5><br />Reset your password by clicking here:<br />".$resetPwdLink;
				$fromEmail = "noreply@stubees.com";  //Make it global Variable
				$fromName = "Stubees";		
				sendEmail($fromEmail, $fromName,$userEmail,$toName,"Reset Password Request",$htmlToSend,0);
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
				$session->errResetpwdemailResetpwd = "inavlid Link, please enter the user id again";
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
					$session->errResetpwdemailResetpwd = "inavlid Link, please enter the user id again";
        			$this->_redirect('login/resetpwd');
        			
        		}
        		// Validate the Time
        		$timeInDb = $row->reset_Pwd_Time;
        		$timeInDb = strtotime($timeInDb);
        		if($timeInDb < (time()-$oneday))
        		{
        			//$this->_helper->FlashMessenger->addMessage("Link expried please enter email id", 'actions');
					$session->errResetpwdemailResetpwd = "Link expried please enter email id";
        			$this->_redirect('login/resetpwd');
        		}
        		else
        		{
        			//Give prompt to reset pwd, update db with new pwd,
        			//nullify corresponding resetpeduuidentry from login tbl table(only after resets the pwd)
				//Since its a valid link,if the user has not been validated yet chaning the status to validated
				$status = $row->user_Status;
				if($status == "nonvalidated") {
				$row->user_Status = "validated";
				$row->save();
				}
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
        	$userEmail = $_POST['email'];
        	// Reset The Password
        	$row = fetchRow('tblLogin','email',$userEmail);
        	$newPwd = sha1($salt. $_POST['email']. $_POST['password']);
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
				
			$emailVerificationLink = "www.stubees.com/scooby/public/Login/useremailvalidation?uuid=".$userValidateUuid."&pid=".$userIndex;
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
						$this->view->goHomeFlag = 1;
					}
				}
				else  //User Exists and status is validated
				{
				   echo "User Account Validated Already";
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
		$emailVerificationLink = "stubees.com/scooby/public/Login/useremailvalidation?uuid=".$userValidateUuid."&pid=".$userIndex;
		$htmlToSend = "<h5>Hello ".$toName." Welcome to Stubees!!</h5><br />Please verify your account by clicking here:<br />".$emailVerificationLink;
		$fromEmail = "noreply@stubees.com";  //Make it global Variable
		$fromName = "Stubees";		
		sendEmail($fromEmail, $fromName,$userEmail,$toName,"Verification",$htmlToSend,0);
    		$this->view->sendValidateEmail = true;
    		return;

    	}
    }

	public function testingAction(){
		// print_scr(D, session_id());
		// $session = new Zend_Session_Namespace();
		
		// if(isset($session->name)){
			// echo "\n DEBUG: Session START: Session name:".$session->name;
		// } else {
			// echo "\n DEBUG: Session is set for the first time";
			// $session->name="ZEND_SESSION";
		// }
		// print_scr(D, session_id());
		// $modname = $this->getRequest()->getModuleName();
		// echo "module name:".$modname.":";
		// phpinfo();
		echo "yo";
		$auth = Zend_Auth::getInstance();
		$authAdapter = new Zend_Auth_Adapter_DbTable(Zend_Db_Table::getDefaultAdapter()); //Getting an instance
		$authAdapter->setTablename('tblLogin')
					->setIdentityColumn('email')
					->setCredentialColumn('password')
					->setIdentity('metalsriks@gmail.com')
					->setCredential('asdasdasd');
		$result = $auth->authenticate($authAdapter);
		if($result->isValid()){
			$storage = new Zend_Auth_Storage_Session();
			$storage->write($authAdapter->getResultRowObject());
			// print_scr(D, "authenticated!");
			echo "authenticated";
		} else {
			//$this->view->errorMessage = "Invalid username or password. Please try again.";
			// print_scr(D, "error authenticating!");
			echo "error authenticating!";
		}
		
	}
}
