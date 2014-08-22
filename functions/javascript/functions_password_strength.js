/**
* Core password strength functions within ILance.
*
* @package      iLance\Javascript\PasswordStrength
* @version	4.0.0.8059
* @author       ILance
*/
var commonPasswords = new Array('password', 'pass', '1234', '1246', '123123', '123'); 
var numbers = "0123456789"; 
var lowercase = "abcdefghijklmnopqrstuvwxyz"; 
var uppercase = "ABCDEFGHIJKLMNOPQRSTUVWXYZ"; 
var punctuation = "!.@$L#*()%~<>{}[]"; 
function checkPassword(password)
{ 
        var combinations = 0; 
        if (contains(password, numbers) > 0)
        { 
                combinations += 10; 
        } 
        if (contains(password, lowercase) > 0)
        { 
                combinations += 26; 
        } 
        if (contains(password, uppercase) > 0)
        { 
                combinations += 26; 
        } 
        if (contains(password, punctuation) > 0)
        { 
                combinations += punctuation.length; 
        } 
     
        // work out the total combinations 
        var totalCombinations = Math.pow(combinations, password.length); 
     
        // if the password is a common password, then everthing changes... 
        if (isCommonPassword(password))
        { 
                totalCombinations = 75000 // about the size of the dictionary 
        } 
     
        // work out how long it would take to crack this (@ 200 attempts per second) 
        var timeInSeconds = (totalCombinations / 200) / 2; 
     
        // this is how many days? (there are 86,400 seconds in a day. 
        var timeInDays = timeInSeconds / 86400 
     
        // how long we want it to last 
        var lifetime = 365; 
     
        // how close is the time to the projected time? 
        var percentage = timeInDays / lifetime; 
     
        var friendlyPercentage = cap(Math.round(percentage * 100), 100);
        
        if (totalCombinations != 75000 && friendlyPercentage < (password.length * 5))
        { 
                friendlyPercentage += password.length * 5; 
        } 
     
        var progressBar = document.getElementById("progressBar"); 
        progressBar.style.width = friendlyPercentage + "%"; 
     
        if (percentage > 1)
        { 
                // strong password 
                progressBar.style.backgroundColor = "#3bce08"; 
                return; 
        } 
     
        if (percentage > 0.5)
        { 
                // reasonable password 
                progressBar.style.backgroundColor = "#ffd801"; 
                return; 
        } 
     
        if (percentage > 0.10)
        { 
                // weak password 
                progressBar.style.backgroundColor = "orange"; 
                return; 
        } 
     
        // useless password! 
        if (percentage <= 0.10)
        { 
                // weak password 
                progressBar.style.backgroundColor = "red"; 
                return; 
        } 
} 
 
function cap(number, max)
{ 
        if (number > max)
        { 
                return max; 
        }
        else
        { 
                return number; 
        } 
} 
 
function isCommonPassword(password)
{ 
        for (i = 0; i < commonPasswords.length; i++)
        { 
                var commonPassword = commonPasswords[i];
                
                if (password == commonPassword)
                { 
                        return true; 
                } 
        } 
 
        return false; 
} 
 
function contains(password, validChars)
{ 
        count = 0; 
     
        for (i = 0; i < password.length; i++)
        { 
                var char1 = password.charAt(i);
                if (validChars.indexOf(char1) > -1)
                { 
                        count++; 
                } 
        }
     
        return count; 
} 

function validatePwd() 
{
	var invalid = " "; // Invalid character is a space
	var minLength = 1; // Minimum length
	var pw1 = document.add_admin.password.value;
	var pw2 = document.add_admin.password2.value;
        
	// check for a value in both fields.
	if (pw1 == '' || pw2 == '') 
	{
		alert('Please enter your password twice.');
		return false;
	}
	// check for minimum length
	if (document.add_admin.password.value.length < minLength) 
	{
		alert('Your password must be at least ' + minLength + ' characters long. Try again.');
		return false;
	}
	// check for spaces
	if (document.add_admin.password.value.indexOf(invalid) > -1) 
	{
		alert("Spaces are not allowed.");
		return false;
	}
	else 
	{
		if (pw1 != pw2) 
		{
			alert ("You did not enter the same new password twice. Please re-enter your password.");
			return false;
		}
   	}
}