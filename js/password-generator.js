/* 
 * Random password generator (JavaScript)
 * 
 * Copyright (c) 2015 Project Nayuki
 * All rights reserved. Contact Nayuki for licensing.
 * https://www.nayuki.io/page/random-password-generator-javascript
 */

"use strict";

// The one and only function called from the HTML code
function generate() {
	var charset = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!\"#$%&'()*+,-./:;<=>?@[\\]^_`{|}~";
	//charset = removeDuplicates(charset);
	charset = charset.replace(/ /, "\u00A0");  // Replace space with non-breaking space
	
	var password = "";
	var length = Math.floor(Math.random() * (16-12) + 12);		

		
	if (length < 0 || length > 10000)
		alert("Invalid password length");
	else {
		for (var i = 0; i < length; i++) password += charset.charAt(randomInt(charset.length));
	}

	$("#password").val(password);
	$("#password").pwstrength("forceUpdate");
	
}

// Returns a random integer in the range [0, n) using a variety of methods
function randomInt(n) {
	var x = randomIntMathRandom(n);
	x = (x + randomIntBrowserCrypto(n)) % n;
	return x;
}


// Not secure or high quality, but always available
function randomIntMathRandom(n) {
	var x = Math.floor(Math.random() * n);
	if (x < 0 || x >= n)
		throw "Arithmetic exception";
	return x;
}


// Uses a secure, unpredictable random number generator if available; otherwise returns 0
function randomIntBrowserCrypto(n) {
	if (typeof Uint32Array == "function" && "crypto" in window && "getRandomValues" in window.crypto) {
		// Generate an unbiased sample
		var x = new Uint32Array(1);
		do window.crypto.getRandomValues(x);
		while (x[0] - x[0] % n > 4294967296 - n);
		return x[0] % n;
	} else
		return 0;
}

function escapeHtml(unsafe) {
        return unsafe
          .replace(/&/g, "&amp;")
          .replace(/</g, "&lt;")
          .replace(/>/g, "&gt;")
          .replace(/"/g, "&quot;")
          .replace(/'/g, "&#039;");
}