<?php 
/*
 * Fellowship One - 2nd Party Plugin
 *
 * A small group finder that depends on FellowshipOne.php to do server-client communication. 
 *
 * Code written by: Bethany Clark of Grace Fellowship Church in Latham, New York
 * Code adapted from: Daniel Boorn (phpsales@gmail.com) 
 */

require_once('FellowshipOne.php');

/*
 * sgfinder is called from the html body once the 'search' input (type='submit') is hit.
 *
 * It also takes in an array of search options to query based on the answers in the form.
 * While this function heavily depends on what's in $searchterms, it's easy enough to modify
 * it when you know what's actually in the array.
 */
function sgfinder($searchterms){
  // Adjust these based on your Application Key, Secret, Church Code, and Portal credentials.
  // Note: get rid of '.staging.' to work in the live environment when your application is ready to launch.
  $settings = array('key'=>'{application-key-goes-here}',
		    'secret'=>'{application-key-goes-here}',
		    'username'=>'{portal-username-here}',
		    'password'=>'{corresponding-password-here}',
		    'baseUrl'=>'https://{churchcode}.staging.fellowshiponeapi.com',
		    'debug'=>false,
		    );
  
  //creates an instance of the FellowshipOne class definied in FellowshipOne.php
  $f1 = new FellowshipOne($settings);
  //attempts to login based on given Portal credentials.
  if(($r = $f1->login()) === false){
    die("Failed to login");
  }
  
  /*
   * sgfinder() takes in an array of $searchterms. The following lines of code looks creates the suffix to
   * append to the URI link that will be called from the Fellowship One servers.
   *
   * You may customize what items you put in searchterms. Here is simply an example where I used searchterms.
   * I would just recommend you keep '/search.json?issearchable=true' as your base.
   */
  $search = '/search.json?issearchable=true';
  if($searchterms['childcare'] == '&hasChildcare=true') {
    $search .= $searchterms['gender'] . $searchterms['maritalstatus'] . $searchterms['childcare'];
  }
  else{
    $search .= $searchterms['gender'] . $searchterms['maritalstatus'];
  }

  // once search suffix has been compiled, call function in FellowshipOne.php to make the server call.
  $groupsList = $f1->getGroupIndividual($search); 

  /*
   * $groupList holds an array of groups that match your search criteria. To list them out on the page,
   * I iterated through the array, and printed the name, description and location of the group in html.
   *
   * You can customize what fields you want displayed based on the json attributes given. See
   * http://developer.fellowshipone.com/docs/groups/v1/Groups.help#search for a list of these attributes.
   */
  foreach($groupsList['groups']['group'] as $tempGroup){ ?>  
    <h1><?php print $tempGroup['name']; ?></h1>
    <p><?php print $tempGroup['description']; ?></p>
    <p><?php print (rtrim($tempGroup['location']['address']['city']) . ', ' . rtrim($tempGroup['location']['address']['stProvince']) . ' ' . rtrim($tempGroup['location']['address']['postalCode'])); ?></p>
    <p></p>
  <?php } //end foreach
} //end sgfinder()
?>

<html>
<head><title>Small Group Finder</title></head>

<body>

  <?php
   /*
    * This form was designed off the needs of my church, but similar to these needs,
    * you can customize this as necessary. No, the groupname, zipcode, and dayoftheweek
    * features have not been developed in sgfinder(). 
    *
    * Each value of the input is designed after the search parameters of the group API realm.
    * ex. haschildcare={'true' or 'false'} has values of 'true' or 'false' in their corresponding
    * input tags.
    *
    * The reason for this is because we can form $searchterms below by the search parameter and its value
    * ex. $searchterm = array( 'childcare' => '&haschildcare=' . $_POST['childcare'] );
    */
  ?>
  <!--start of small group finder form -->	
    <form name="input" action="{the full path of this file}" method="post">
      <h6>Group Name:</h6>
      <input type="text" name="groupname" />
      <input type="submit" name="search" value="Search" />

      <h6>Gender:</h6>
      <input type="radio" name="gender" value="0" checked="checked" /> Coed<br />
      <input type="radio" name="gender" value="1" /> Male Only<br />
      <input type="radio" name="gender" value="2" /> Female Only<p> </p>

      <h6>Marital Status:</h6>
      <input type="radio" name="maritalstatus" value="0" checked="checked" /> Couples and Singles<br />
      <input type="radio" name="maritalstatus" value="1" /> Couples Only<br />
      <input type="radio" name="maritalstatus" value="2" /> Singles Only<p> </p>

      <h6>Childcare:</h6>
      <input type="radio" name="childcare" value="false" checked="checked" /> Not Needed<br />
      <input type="radio" name="childcare" value="true" /> Needed<p> </p>

      <h6>Zip Code:</h6>
      <input type="text" name="zipcode" /><p> </p>
 
      <h6>Days Available:</h6>    
      <input type="checkbox" name="dayoftheweek" value="0" checked="checked" /> Sunday<br />
      <input type="checkbox" name="dayoftheweek" value="1" checked="checked" /> Monday<br />
      <input type="checkbox" name="dayoftheweek" value="2" checked="checked" /> Tuesday<br />
      <input type="checkbox" name="dayoftheweek" value="3" checked="checked" /> Wednesday<br />
      <input type="checkbox" name="dayoftheweek" value="4" checked="checked" /> Thursday<br />
      <input type="checkbox" name="dayoftheweek" value="5" checked="checked" /> Friday<br />
      <input type="checkbox" name="dayoftheweek" value="6" checked="checked" /> Saturday <p> </p>
    </form>
  <!--end of form -->
  

  <?php
  /*
   * The first time you visit the page, no SG details will be shown. However, once you press the 'search'
   * button, the search terms are compiled from the options of the above form. Then sgfinder() is called.
   */
    if(isset($_POST["search"])) { 
      $searchterms = array('gender' => ('&genderID=' . $_POST['gender']),
		       'maritalstatus' => ('&maritalStatusID=' . $_POST['maritalstatus']),
		       'childcare' => ('&hasChildcare=' . $_POST['childcare']),
		       );
      sgfinder($searchterms); 
    } 
    ?>

</body>
</html>