<?php

function test () {

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

	foreach ($endpoints as $endpoint => $params) {
		
	}

}

?>
