<?php

use App\User;
use Illuminate\Support\Facades\Route;
$tokenPath = 'token.json';

function getClient(){

    $client = new Google_Client();
    $client->setApplicationName('Google Classroom API PHP Quickstart');
    $client->setScopes([
        Google_Service_Classroom::CLASSROOM_COURSES,
        Google_Service_Classroom::CLASSROOM_PROFILE_EMAILS,
        Google_Service_Classroom::CLASSROOM_PROFILE_PHOTOS,
        Google_Service_Classroom::CLASSROOM_TOPICS,
        Google_Service_Classroom::CLASSROOM_COURSEWORK_STUDENTS,
        Google_Service_Classroom::CLASSROOM_ROSTERS,
    ]);
    $client->setAuthConfig('credentials.json');
    $client->setAccessType('offline');
    return $client;
}

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () use($tokenPath) {
    $user = User::first();
    $user->setClient();
    return redirect($user->_client->promptForToken()['url']);
});


Route::get('/callback', function () use($tokenPath) {
    $authCode = request('code');
    $user = User::first();
    $user->setClient();
   return redirect($user->_client->setAuthToken($authCode)['url']);

});
Route::get('/classes', function () use($tokenPath) {
    $user = User::first();
    $user->setClient();

    $service = new Google_Service_Classroom($user->_client->getClient());
    // Print the first 10 courses the user has access to.
    $optParams = array(
        'pageSize' => 10
    );
    $results = $service->courses->listCourses($optParams);
    if (count($results->getCourses()) == 0) {
        print "No courses found.\n";
    } else {
        print "Courses:\n";
        foreach ($results->getCourses() as $course) {
           $students =  $service->courses_students->listCoursesStudents($course->getId());
            foreach ($students as $student) {
                echo '<pre>';
                print_r($student->getProfile());
            }
        }
    }
});
