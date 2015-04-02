<?php

require './jakephp/Model.php';

/* 

EXAMPLE: 

class User extends Model {
	public $email;
	public $username;
	public $password; 
	public $created_at; 
	public $session_id; 
}

*/

class User extends Model {
	public $email;
	public $first_name;
	public $last_name;
	public $school_id;
	public $password;
	public $created_at;
}

class School extends Model {
	public $domain;
	public $name;
	public $created_at; 
}

class Course extends Model {
	public $name;
	public $description; 
	public $school_id;
	public $created_at; 
}

class Enrollment extends Model {
	public $user_id; 
	public $course_id; 
	public $created_at; 
}

class Question extends Model {
	public $content;
	public $likes;
	public $created_at;
	public $user_id;
	public $course_id;
}

class Answer extends Model {
	public $content;
	public $likes;
	public $created_at;
	public $user_id;
	public $question_id;
}

class QuestionLike extends Model {
	public $user_id;
	public $question_id; 
	public $created_at; 
}

class AnswerLike extends Model {
	public $user_id;
	public $answer_id; 
	public $created_at; 
}

?>