<?php
require_once dirname(__FILE__) . '/helpers.php';

function tm_open_ai_request($model, $text_length, $temperature, $prompt)
{
  
  $url = "https://api.openai.com/v1/completions";

  $curl = curl_init($url);
  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_POST, true);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

  $headers = array(
    "Content-Type: application/json",
    "Authorization: Bearer " . get_option('tm_openai_key'),
  );
  curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

  if (!empty($model)) {
    $data =  [
      "model" => $model,
      "prompt" => $prompt,
      "temperature" => floatval($temperature),
      "max_tokens" => intval($text_length),
      "top_p" => 1,
      "frequency_penalty" => 0,
      "presence_penalty" => 0
    ];

    $encoded_prompt = json_encode($data);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $encoded_prompt);

    //for debug only!
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

    $resp = curl_exec($curl); 
    curl_close($curl);
	  
    return json_decode($resp)->choices[0]->text;
  } else {
    return;
  }
}

function tm_open_ai_generate_introduction($prompt)
{
  $model = get_option('tm_openai_model_for_introduction');
  $text_length = get_option('tm_openai_text_length_for_introduction');
  $temperature = get_option('tm_openai_temperature_for_introduction');
  $output = tm_open_ai_request($model, $text_length, $temperature, $prompt);

  return tm_open_ai_format_paragraph($output);
}

function tm_open_ai_generate_before_amazon_list($prompt)
{
  $model = get_option('tm_openai_model_for_before_amazon_list');
  $text_length = get_option('tm_openai_text_length_for_before_amazon_list');
  $temperature = get_option('tm_openai_temperature_for_before_amazon_list');
  $output = tm_open_ai_request($model, $text_length, $temperature, $prompt);

  return tm_open_ai_format_paragraph($output);
}

function tm_open_ai_generate_review($prompt)
{
  $model = get_option('tm_openai_model_for_review');
  $text_length = get_option('tm_openai_text_length_for_review');
  $temperature = get_option('tm_openai_temperature_for_review');
  $output = tm_open_ai_request($model, $text_length, $temperature, $prompt);

  return tm_open_ai_format_paragraph($output);
}

function tm_open_ai_generate_buy_guide($prompt)
{
  $model = get_option('tm_openai_model_for_buy_guide');
  $text_length = get_option('tm_openai_text_length_for_buy_guide');
  $temperature = get_option('tm_openai_temperature_for_buy_guide');
  $output = tm_open_ai_request($model, $text_length, $temperature, $prompt);
  return $output;
//   return tm_open_ai_format_paragraph($output);
}

function tm_open_ai_generating_question($keyword)
{
  $generating_question_prompt = tm_open_ai_replace_with_keyword_and_keyword_stripped($keyword, get_option('tm_openai_prompt_text_for_generating_question'));
  $model = get_option('tm_openai_model_for_generating_question');
  $text_length = get_option('tm_openai_text_length_for_generating_question');
  $temperature = get_option('tm_openai_temperature_for_generating_question');
  $output = tm_open_ai_request($model, $text_length, $temperature, $generating_question_prompt);

  return trim($output);
}

function tm_open_ai_get_answer($prompt)
{
  $model = get_option('tm_openai_model_for_get_answer');
  $text_length = get_option('tm_openai_text_length_for_get_answer');
  $temperature = get_option('tm_openai_temperature_for_get_answer');
  $output = tm_open_ai_request($model, $text_length, $temperature, $prompt);

  return $output;
}

function tm_open_ai_generate_conclusion($prompt)
{
  $model = get_option('tm_openai_model_for_conclusion');
  $text_length = get_option('tm_openai_text_length_for_conclusion');
  $temperature = get_option('tm_openai_temperature_for_conclusion');
  $output = tm_open_ai_request($model, $text_length, $temperature, $prompt);

  return tm_open_ai_format_paragraph($output);
}

function tm_open_ai_generate_qna($keyword)
{
  $questions = tm_open_ai_generating_question($keyword);

  $questions_list = explode("?", trim($questions));
  $count = 0;
  $qnas = [];
  foreach ($questions_list as $question) {

    if ($question && $count <= 4) {
      $full_question = trim($question) . '?';
      $answer = tm_open_ai_get_answer($full_question);

      $qna = [
        'question' => $full_question,
        'answer' => str_replace('?', '', trim(preg_replace('/\s+/', ' ', $answer)))
      ];

      array_push($qnas, $qna);
    }
    $count++;
  }

  return $qnas;
}


function tm_open_ai_format_paragraph($output)
{
  $exploded = explode(".", $output);

  $length = count($exploded);

  $introduction_text = '';

  if ($length && $length >= 2) {
    foreach ($exploded as $key => $sentence) {
      if ($key != ($length - 1)) {
        if ($key == round($length / 2)) {
          $introduction_text .= '<br/><br/>';
        }
        $introduction_text .= trim($sentence) . '. ';
      }
    }
  }

  return "<p>" . ucfirst($introduction_text) . "</p>";
}
