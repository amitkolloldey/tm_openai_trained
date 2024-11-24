<?php

/*
Modified to use CURL : Sameer Borate
Original code Copyright (c) 2009 Ulrich Mierendorff

Permission is hereby granted, free of charge, to any person obtaining a
copy of this software and associated documentation files (the "Software"),
to deal in the Software without restriction, including without limitation
the rights to use, copy, modify, merge, publish, distribute, sublicense,
and/or sell copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
DEALINGS IN THE SOFTWARE.
*/


/*

  More information on the authentication process can be found here:
  http://docs.amazonwebservices.com/AWSECommerceService/latest/DG/BasicAuthProcess.html

  Modified by Andreas Itzchak Rehberg, rawly following http://wern-ancheta.com/blog/2013/02/10/getting-started-with-amazon-product-advertising-api/

*/


/** Access the Amazon API and obtain data
 * @package Amazon
 * @function aws_signed_request
 * @param string region Locale for the Amazon site to query (e.g. 'de' or 'com')
 * @param array params Search parameters
 * @param string public_key AWS public key
 * @param string private_key AWS private key
 * @param string associate_tag Amazon PartnerNet ID
 * @log 2011-10-19 last changes by CodeDiesel
 * @log 2014-04-21 reformatted, ApiDoc added (Izzy)
 * @log 2014-04-22 additions as described at <a href='http://wern-ancheta.com/blog/2013/02/10/getting-started-with-amazon-product-advertising-api/' title='Getting started with Amazon Product Advertizing API'>Wern-Ancheta</A> (Izzy)
 */
function  aws_signed_request($region, $params, $public_key, $private_key, $associate_tag)
{

  $method = "GET";
  if ($region == 'jp') {
    $host = "ecs.amazonaws." . $region;
  } else {
    $host = "webservices.amazon." . $region; // must be in small case
  }
  $uri = "/onca/xml";

  $params["Service"]          = "AWSECommerceService";
  $params["AWSAccessKeyId"]   = $public_key;
  $params["AssociateTag"]     = $associate_tag;
  $params["Timestamp"]        = gmdate("Y-m-d\TH:i:s\Z");
  $params["Version"]          = "2009-03-31";

  /* The params need to be sorted by the key, as Amazon does this at
      their end and then generates the hash of the same. If the params
      are not in order then the generated hash will be different thus
      failing the authetication process.
    */
  ksort($params);

  $canonicalized_query = array();

  foreach ($params as $param => $value) {
    $param = str_replace("%7E", "~", rawurlencode($param));
    $value = str_replace("%7E", "~", rawurlencode($value));
    $canonicalized_query[] = $param . "=" . $value;
  }

  $canonicalized_query = implode("&", $canonicalized_query);

  $string_to_sign = $method . "\n" . $host . "\n" . $uri . "\n" . $canonicalized_query;

  /* calculate the signature using HMAC with SHA256 and base64-encoding.
       The 'hash_hmac' function is only available from PHP 5 >= 5.1.2.
    */
  $signature = base64_encode(hash_hmac("sha256", $string_to_sign, $private_key, True));

  /* encode the signature for the request */
  $signature = str_replace("%7E", "~", rawurlencode($signature));

  /* create request */
  $request = "http://" . $host . $uri . "?" . $canonicalized_query . "&Signature=" . $signature;

  /* I prefer using CURL */
  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $request);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_TIMEOUT, 30);
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

  $xml_response = curl_exec($ch);
  usleep(2000000);

  if ($xml_response === False) {
    curl_close($ch);
    return False;
  } else {
    /* parse XML */
    $parsed_xml = @simplexml_load_string($xml_response);
    curl_close($ch);
    return ($parsed_xml === False) ? False : $parsed_xml;
  }
}