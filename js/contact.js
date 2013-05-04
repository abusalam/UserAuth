function limitChars(textarea, limit, infodiv)
{
	var text = textarea.value; 
	var textlength = text.length;
	var info = document.getElementById(infodiv);
	if(textlength > limit)
	{
		info.innerHTML = ' (You cannot write more then '+limit+' characters!)';
		textarea.value = text.substr(0,limit);
		return false;
	}
	else
	{
		info.innerHTML = ' (You have '+ (limit - textlength) +' characters left.)';
		return true;
	}
}

function checkemail(str){
	var testresults=false;
	var filter=/^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i;
	if (filter.test(str))
		testresults=true;
	else {
		testresults=false;
	}
	return (testresults);
}

function do_submit(){
  var u_name=document.feed_frm.v_name.value;
  var v_email=document.feed_frm.v_email.value;
  var fd_txt=document.feed_frm.feed_txt.value;
  if(u_name.length==0)
  {
   window.alert("Please give your name!");
  }
  else if(fd_txt.length==0)
  {
   window.alert("Please write your comment!");
  }
  else if(v_email.length==0)
  {
   window.alert("Please give your Email-Id!");
  }
  else if(checkemail(v_email)!=true)
  {
   window.alert("Please input a valid email address!");
  }
  else
   document.feed_frm.submit();
}








