<?php
require_once 'ApiData.php';
use ChateGPT\ApiData;

include "header.php";
$postContent = NULL;
$post_title = NULL;
global $wpdb;

$type = 'Y-m-d H:i:s';
$sequenceTime = HOUR_IN_SECONDS * 10; //post per hrs.
//	$sequenceTime = 55; //post per hrs.
$postTime = current_time('timestamp') + $sequenceTime;
$timeToPost = wp_date($type, $postTime);



$tablename = $wpdb->prefix.'chatgpt_content_writer';
$sql = "SELECT * FROM $tablename";

$results = $wpdb->get_results($sql);
//$getApiToken = $results[0]->api_token;
//$getTemperature = intval($results[0]->temperature);
//$getMaxTokens = intval($results[0]->max_tokens);
$getLanguage = $results[0]->language;

$languages = array("tr","en");
if(in_array($getLanguage,$languages)) {
	include "language/".$getLanguage.".php";
} else {
	include "language/en.php";
}


if(isset($_POST['goTest'])){
	$TEXT = $_POST["chatGptText"];
//	$header = array(
//		'Authorization: Bearer '.$getApiToken,
//		'Content-type: application/json; charset=utf-8',
//	);
//	$params = json_encode(array(
//		'prompt'		=> $TEXT,
//		'model'			=> 'text-davinci-003',
//		'temperature'	=> $getTemperature,
//		'max_tokens' => $getMaxTokens,
//	));
//	$curl = curl_init('https://api.openai.com/v1/completions');
//	$options = array(
//		CURLOPT_POST => true,
//		CURLOPT_HTTPHEADER =>$header,
//		CURLOPT_POSTFIELDS => $params,
//		CURLOPT_RETURNTRANSFER => true,
//	);
//	curl_setopt_array($curl, $options);
//	$response = curl_exec($curl);
//	$httpcode = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
//	if(200 == $httpcode){
//		$json_array = json_decode($response, true);
//		$choices = $json_array['choices'];
//		$postContent = $choices[0]["text"];
//	}


	$data = new ApiData($wpdb);
    // Get content from API and create a new post
	$post_title = $data->getDetailByText($TEXT);//array => title, keyword
//    $post_title = $array['title'] ?? '';
//    $keyword = $array['keyword'] ?? '';
    if(isset($post_title)) {
	    $postContent = $data->getDescriptions($post_title);
    }
}

if(isset($_POST["addBlog"])){
	$my_post = array();
	$my_post['post_title']    = $_POST["postTitle"];
	$my_post['post_content']  = $_POST["postContent"];
	$my_post['tags_input']  = $_POST["postKeywords"];
	$my_post['post_status']   = $_POST['postStatus'];
	$my_post['post_author']   = 1;
	$my_post['post_category'] = array($_POST["postCategory"]);
    $my_post['post_date'] = $_POST['postDate'];
	// Insert the post into the database
	//Here is the Magic:
	kses_remove_filters(); //This Turns off kses
	$post_id = wp_insert_post($my_post);
	kses_init_filters(); //This Turns on kses again
}


?>

<form method="post">
	<br>
	<div class="mb-3">
		<label class="form-label"><?php echo $lang["chatGptText"]; ?></label>
		<textarea class="form-control" id="chatGptText" name="chatGptText" rows="3"></textarea>
	</div>
	<button type="submit" name="goTest" class="btn btn-secondary"><?php echo $lang["testButton"]; ?></button><br><br>

	<div class="mb-3">
		<label class="form-label"><?php echo $lang["blogTitle"]; ?></label>
		<input type="text" name="postTitle" value="<?php echo $post_title; ?>" id="postTitle" class="form-control"/>
	</div>
	<div class="mb-3">
		<label class="form-label"><?php echo $lang["blogContent"]; ?></label>
		<textarea style="height:250px;" class="form-control" name="postContent" id="postContent" rows="3"><?php echo $postContent; ?></textarea>
		<small><?php echo $lang["blogContentDesc"]; ?></small>
	</div>
	<div class="mb-3">
		<label class="form-label"><?php echo $lang["blogCategory"]; ?></label>
		<select name="postCategory" id="postCategory" class="form-select">
			<?php
			$categories = get_categories(array( 'hide_empty' => 0 ));
			foreach ($categories as $category) {
				echo '<option value="' . $category->term_id . '">' . $category->name . '</option>';
			}
			?>
		</select>
	</div>
    <div class="mb-3">
        <label class="form-label">Post Type</label>
        <input type="text" name="postStatus" value="future" id="postStatus" class="form-control"/>
    </div>
    <div class="mb-3">
        <label class="form-label">Time for post</label>
        <input type="text" name="postDate" value="<?php echo  $timeToPost ?>" id="postDate" class="form-control"/>
    </div>

	<div class="mb-3">
		<label class="form-label"><?php echo $lang["blogKeywords"]; ?></label>
		<textarea class="form-control" name="postKeywords" id="postKeywords" rows="3"></textarea>
	</div>

	<button type="submit" name="addBlog" class="btn btn-success"><?php echo $lang["addBlogButton"]; ?></button>
</form>