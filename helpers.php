<?php

function tm_open_ai_replace_with_keyword_and_keyword_stripped($keyword, $given_prompt_text)
{
  $new_keyword = strtolower(trim($keyword));
  $first_word_check = strtok($new_keyword, " ");
  $keyword_stripped = $first_word_check === 'best' ? str_replace($first_word_check, '', $new_keyword) : $new_keyword;

  $prompt_string = ['[keyword]', '[keyword_stripped]'];
  $prompt_replace_text = [$keyword, $keyword_stripped];

  return str_replace($prompt_string, $prompt_replace_text, $given_prompt_text);
}
