<?php
include "./settings.php";

// optionally include some usefull functions
include "helpers.php";

// API Endpoints

function github_push()  {
    $text = "" . shell_exec("git pull");
    require "Twilio/Services/Twilio.php";
    $AccountSid = "ACbd652dd257ef5f7fdbf246a6e7af8d3a";
    $AuthToken = "e22f767658650152da61ff7dc93ad57e";
    $client = new Services_Twilio($AccountSid, $AuthToken);
    $sms = $client->account->messages->sendMessage(
        "516-210-4617", 
        "5163535851",
        "Message: " . $text
    );
    echo $text; 
}

function test() {
	echo "it worked (testing)!!"; 
}

function createUser() {
	if (_validate("createUser")) {
		$user = new User();
		
		if (sizeof($user->search("email", $_GET["email"])) == 0) {
			$user->email = $_GET["email"];
			$user->first_name = $_GET["first_name"];
			$user->last_name = $_GET["last_name"];
			$user->password = $_GET["password"];
			$user->created_at = time();
			$domain = substr(strrchr($_GET["email"], "@"), 1);

			if (trim($domain) == "" or isset($domain) == False) {
				return _error("Please use a valid email.");
			} else {
				$user->school_id = createSchool($domain); 
				$user->save(); 
				$user->get("email", $_GET["email"]);

				return _respond($user); 
			}
		} else {
			return _error("A user with that email already exists.");
		}
	}
}

function logIn() {
	if (_validate("logIn")) {
		$user = new User(); 

		if (sizeof($user->search("email", $_GET["email"])) == 0) {
			return _error("A user with that email doesn't exists.");
		} else {
			$user->get("email", $_GET["email"]); 
			if ($user->password == $_GET["password"]) {
				return _respond($user); 
			} else {
				return _error("Incorrect Password"); 
			}
		}

	}
}

function createSchool($domain) {
	$school = new School(); 
	
	if (sizeof($school->search("domain", $domain)) == 0) {
		$school->domain = $domain; 
		$school->created_at = time(); 
		$school->save(); 
	}

	$school->get("domain", $domain);

	return $school->id; 

}

function getAllSchools() {

}

function enrollUserInSchool() {

}

function createCourseInSchool() {

}

function getAllCoursesInSchool() {

}

function enrollUserInCourse() {
	if (_validate("enrollUserInCourse")) {
		
		$course_name = $_GET["course_name"];
		$user_id = $_GET["user_id"];
		
		$user = new User(); 
		$user->get("id", $user_id);

		$school_id = $user->school_id;
		
		// create course if doesn't exist
		$course = new Course();
		$search = $course->match(["name" => $course_name, "school_id" => $school_id]);


		
		if (sizeof($search) == 0) {
			$course->name = $course_name; 
			$course->school_id = $school_id;
			$course->created_at = time();
			$course->save(); 
			$search = $course->match(["name" => $course_name, "school_id" => $school_id]);
		}

		$course->get("id", $search[0]["id"]);

		$course_id = $course->id; 

		$enrollment = new Enrollment(); 

		$search = $enrollment->match(["course_id" => $course_id, "user_id" => $user_id]);

		if (sizeof($search) == 0) {
			$enrollment->user_id = $user_id; 
			$enrollment->course_id = $course_id;
			$enrollment->created_at = time();
			$enrollment->save(); 
			return _respond($enrollment);
		} else {
			return _error("You're already in that class!"); 
		}

	}
}

function getUserCourses() {
	if (_validate("getUserCourses")) {
		$enrollment = new Enrollment(); 
		$enrollments = $enrollment->search("user_id", $_GET["user_id"]);

		$course_ids = []; 

		foreach ($enrollments as $enrollment) { 
			array_push($course_ids, $enrollment["course_id"]); 
		}

		if (sizeof($course_ids) > 0) {
			$course = new Course(); 
			$courses = $course->getMultiple("id", $course_ids);

			for ($i=0; $i < sizeof($courses); $i++) { 
				$id = $courses[$i]["id"]; 
				$time = $courses[$i]["created_at"];

				$date = date("m/d/Y", $time); 

				$enrollment = new Enrollment(); 
				$num_ppl = sizeof($enrollment->search("course_id", $id));

				//$courses[$i]["description"] = "{$num_ppl} enrolled. Created {$date}";
				$courses[$i]["description"] = "Members: {$num_ppl}";
			}

			return _respond($courses); 
		} else {
			return _error("You aren't in any courses!"); 
		}
	}
}

function dropUserFromCourse() {
	if (_validate("enrollUserInCourse")) {
		
		$course_name = $_GET["course_name"];
		$user_id = $_GET["user_id"];
		
		$user = new User(); 
		$user->get("id", $user_id);

		$school_id = $user->school_id;
		
		// create course if doesn't exist
		$course = new Course();
		$courses = $course->match(["name" => $course_name, "school_id" => $school_id]);

		if (sizeof($courses) == 0) {
			return _error("That course doesn't exist");
		}

		$course->get("id", $courses[0]["id"]);

		$course_id = $course->id; 

		$enrollment = new Enrollment(); 
		$search = $enrollment->match(["course_id" => $course_id, "user_id" => $user_id]);

		if (sizeof($search) == 0) {
			return _error("You aren't in that class!");
		} else {
			$enrollment->get("id", $search[0]["id"]);
			$enrollment->delete();
			return _respond("You successfully dropped {$course_name}.");
		}

	}
}

function postQuestion() {
	if (_validate("postQuestion")) {
		$question = new Question(); 
		$question->content = $_GET["question"];
		$question->likes = 0;
		$question->created_at = time();
		$question->user_id = $_GET["user_id"];
		$question->course_id = $_GET["course_id"];
		
		$result = False; 

		$result = $question->save(); 

		if ($result) {
			return _respond($question); 
		} else {
			return _error("Internal Server Error."); 
		}

		
	}
}

function getCourseQuestions() {
	if (_validate("getCourseQuestions")) {
		$question = new Question();
		$questions = $question->search("course_id", $_GET["course_id"]);

		for ($i=0; $i < sizeof($questions); $i++) { 
			$user = new User(); 
			$user->get("id", $questions[$i]["user_id"]);
			$questions[$i]["user_name"] = $user->first_name . " " . $user->last_name;

			$like = new QuestionLike(); 
			$likes = $like->search("question_id", $questions[$i]["id"]);
			$questions[$i]["likes"] = sizeof($likes);
		}

		return _respond($questions); 
	}
}

function likeQuestion() {
	if (_validate("likeQuestion")) {
		$like = new QuestionLike(); 
		$likes = $like->match(["user_id" => $_GET["user_id"],"question_id" => $_GET["question_id"]]); 
		if (sizeof($likes) == 0) {
			$like->user_id = $_GET["user_id"]; 
			$like->question_id = $_GET["question_id"]; 
			$like->created_at = time();
			$like->save(); 
			return _respond($like); 
		} else {
			return _error("You already liked this question.");
		}
	}
}

function postAnswer() {
	if (_validate("postAnswer")) {
		$result = False; 

		$answer = new Answer(); 
			$answer->content = $_GET["answer"]; 
			$answer->likes = 0; 
			$answer->created_at = time(); 
			$answer->user_id = $_GET["user_id"]; 
			$answer->question_id = $_GET["question_id"]; 
			
			$result = $answer->save(); 
		
			if ($result) {
				return _respond($answer);
			} else {
				return _error("Internal Server Error."); 
			}

			
	}
}

function getQuestionAnswers() {
	if (_validate("getQuestionAnswers")) {
		$answer = new Answer();
		$answers = $answer->search("question_id", $_GET["question_id"]);

		for ($i=0; $i < sizeof($answers); $i++) { 
			$user = new User(); 
			$user->get("id", $answers[$i]["user_id"]);
			$answers[$i]["user_name"] = $user->first_name . " " . $user->last_name;

			$like = new AnswerLike(); 
			$likes = $like->search("answer_id", $answers[$i]["id"]);
			$answers[$i]["likes"] = sizeof($likes);
		}

		return _respond($answers); 
	}
}

function likeAnswer() {
	if (_validate("likeAnswer")) {
		$like = new AnswerLike(); 
		
		$likes = $like->match(["user_id" => $_GET["user_id"],"answer_id" => $_GET["answer_id"]]); 

		if (sizeof($likes) == 0) {
			$like->user_id = $_GET["user_id"]; 
			$like->answer_id = $_GET["answer_id"]; 
			$like->created_at = time();
			$like->save(); 
			return _respond($like); 
		} else {
			return _error("You already liked this answer.");
		}
	}
}

// helpers

function courseExists() {

}

function userExists() {

}

function schoolExists() {

}



// Must include this function. You can change its name in settings.php
function home() {
	// CODE HERE
	include("views/home.php"); 
}

// Must include this function. You can change its name in settings.php
function notfound() {
	// CODE HERE

	include("views/notfound.php"); 
}

/*  
	FUNCTIONS
	Note:	functions that begin with '_' are not expected to return pages, 
			and are reserved for user defined functions. 
	Ex: 	"_logUserIn()" or "_getPostsForUser($username)" 
*/  

function getEndpoints() {
	$endpoints = []; 
	$endpoints["createUser"] = ["email", "first_name", "last_name", "password"];
	$endpoints["logIn"] = ["email", "password"];
	$endpoints["enrollUserInCourse"] = ["user_id", "course_name"];
	$endpoints["getUserCourses"] = ["user_id"];
	$endpoints["postQuestion"] = ["user_id", "course_id", "question"];
	$endpoints["getCourseQuestions"] = ["course_id"];
	$endpoints["postAnswer"] = ["user_id", "question_id", "answer"];
	$endpoints["getQuestionAnswers"] = ["question_id"];
	$endpoints["likeQuestion"] = ["user_id", "question_id"];
	$endpoints["likeAnswer"] = ["user_id", "answer_id"];

	return $endpoints; 
}

function _randstr($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

function tests() {

	$endpoints = []; 

	// create user
	$email = _randstr() . "@" . _randstr() . ".edu";
	$name = _randstr(); 
	$_GET["email"] = $email; 
	$_GET["first_name"] = $name;
	$_GET["last_name"] = "test";
	$_GET["password"] = "test";
	$endpoints["createUser"] = createUser(); 

	// log in
	echo "<br><br>";
	$endpoints["logIn"] = logIn();

	// enroll user in course
	$_GET["user_id"] = 1; 
	$_GET["course_name"] = _randstr(); 
	echo "<br><br>";
	$endpoints["enrollUserInCourse"] = enrollUserInCourse();
	
	// get courses
	$endpoints["getUserCourses"] = getUserCourses();
	
	// post question
	$_GET["course_id"] = 1; 
	$_GET["question"] = _randstr(); 
	echo "<br><br>";
	$endpoints["postQuestion"] = postQuestion();

	// post anser
	$_GET["question_id"] = 1; 
	$_GET["answer"] = _randstr(); 
	echo "<br><br>";
	$endpoints["postAnswer"] = postAnswer();

	echo "<br><br>";
	foreach ($endpoints as $key => $value) {
		echo "{$key} => {$value['error']}<br>";
	}

}

function _validate($endpoint) {

	$endpoints = []; 
	$endpoints["createUser"] = ["email", "first_name", "last_name", "password"];
	$endpoints["logIn"] = ["email", "password"];
	$endpoints["enrollUserInCourse"] = ["user_id", "course_name"];
	$endpoints["getUserCourses"] = ["user_id"];
	$endpoints["postQuestion"] = ["user_id", "course_id", "question"];
	$endpoints["getCourseQuestions"] = ["course_id"];
	$endpoints["postAnswer"] = ["user_id", "question_id", "answer"];
	$endpoints["getQuestionAnswers"] = ["question_id"];
	$endpoints["likeQuestion"] = ["user_id", "question_id"];
	$endpoints["likeAnswer"] = ["user_id", "answer_id"];

	for ($i=0; $i < sizeof($endpoints[$endpoint]); $i++) { 
		$attribute = $endpoints[$endpoint][$i]; 

		if (isset($_GET[$attribute]) && trim($_GET[$attribute]) != "") {
			$_GET[$attribute] = trim($_GET[$attribute]); 

			if ($attribute == "email" or $attribute == "first_name" or $attribute == "last_name") {
				$_GET[$attribute] = strtolower($_GET[$attribute]);
			}

		} else {
			_error("missing " . $endpoints[$endpoint][$i]);
			return False; 
		}
	}

	return True; 
}

function _respond($input) {
	$array = []; 
	$array["error"] = False;
	$array["error_message"] = ""; 
	$array["data"] = $input; 
	echo json_encode($array);
	return $array;
}

function _error($message) {
	$array = []; 
	$array["error"] = True; 
	$array["error_message"] = $message; 
	$array["data"] = []; 
	echo json_encode($array); 
	return $array;
}

// Useful for system wide announcments / debugging
function _everypage() {

}

?>