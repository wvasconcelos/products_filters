// JavaScript Document
function GetURLParametersArray(){
	/* RETURNS AN ARRAY CONTAINING THE
	 * URL's PARAMETERS (KEY/VALUES).
	 ******************************/
	var parameters = {};
	window.location.search
		.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(str,key,value) {
			parameters[key] = value;
  		}
	);
	return parameters;
}

function AddURLParameter( key, value ){
	/* ADDS A PARAMETER (KEY/VALUE PAIR)
	 * TO THE CURRENT URL.
	 ***********************************/
	var parameters = GetURLParametersArray();
	var newURL = "index.php?";
	
	//replace main_page
	parameters["main_page"] = "products_filters";
	
	//Add/replace parameter/value pair
	parameters[ key ] = value;
	
	//concatenate the new url string
	for(var k in parameters){
		newURL += k + "=" + parameters[k] + "&";
	}
	//remove the last ampersand or the question mark
	newURL = newURL.substring(0, (newURL.length - 1) );
	
	return newURL;
}

function RemoveURLParameter( key ){
	/* REMOVES A PARAMETER (KEY=VALUE)
	 * FROM THE CURRENT URL.
	 ******************************/
	var parameters = GetURLParametersArray();
	var newURL = "index.php?";
	//concatenate the new url string
	for(var k in parameters){
		if( k != key ){
			newURL += k + "=" + parameters[k] + "&";
		}
	}
	newURL = newURL.substring(0, (newURL.length - 1) ); //remove the last ampersand / question mark
	
	return newURL;
}

function getURLFileName() {
	/* RETRIEVE THE FILE NAME
	 * FROM THE CURRENT URL.
	 ************************/
    var url = document.URL;
    var path = url.substring(url.lastIndexOf("/")+1);
	if( url.lastIndexOf("?") > 0 ){
		path = path.substring(0, path.lastIndexOf("?"));
	}
	
    return path;
}

function CollapseToggle(id){
	/* HANDLES COLLAPSE FILTER REQUEST
	 *********************************/
	if( $("#collapse-" + id).hasClass("collapsed") ){
		$("#collapse-" + id).removeClass( "collapsed" );
		$("#collapse-" + id).addClass( "collapsible" );
		$("#filter-" + id).css("display","none");
		$("#collapse-" + id).css("background-color","WhiteSMoke");
		$("#collapse-" + id).css("margin-top","5px");
	}else{
		$("#collapse-" + id).removeClass( "collapsible" );
		$("#collapse-" + id).addClass( "collapsed" );
		$("#filter-" + id).css("display","block");
		$("#collapse-" + id).css("background-color","White");
		$("#collapse-" + id).css("margin-top","20px");
	}
}

function CollapseFilters(){
	/* HANDLES COLLAPSE ALL FILTERS
	 ******************************/
	if( $("#filterTitle").hasClass("collapsed") ){
		$("#filterTitle").removeClass( "collapsed" );
		$("#filterTitle").addClass( "collapsible" );
		$("#filters").css("display","none");
	}else{
		$("#filterTitle").removeClass( "collapsible" );
		$("#filterTitle").addClass( "collapsed" );
		$("#filters").css("display","block");
	}
}

function UpdateFilters(){ 
	/* PREPARES NEW URL BASED
	 * ON FILTER SELECTION AND
	 * REFRESH PAGE.
	 **************************/
	var cbx = $('input[name=filter-values\\[\\]]');
	var cPath = $('input[name=cPath').val();
	var fvals = "";
	for( var i = 0; i < cbx.length; i++ ){
		if( cbx[i].checked ){
			fvals += cbx[i].value + "_";
		}
	}
	//trim the last underscore
	fvals = fvals.substring( 0, fvals.length - 1 );
	var newURL;
	
	if( fvals != "" ){
		newURL = AddURLParameter( "fv", fvals );
	}else{
		newURL = RemoveURLParameter( "fv" );
	}
	
	if( cPath != "" && newURL.search("cPath") <= 0 ){
		newURL += "&cPath=" + cPath;
	}
	
	window.location = newURL;
}

function UpdateOther(parameter){
	/* PREPARES A NEW URL BASED ON
	 * NATIVE FILTER AND REDIRECT.
	 ********************************/
	var parameter_name = GetParameterFullName( parameter );
	var cPath	= $('input[name=cPath').val();
	var cbx		= $('input[name=' + parameter_name + '\\[\\]]');
	var fvals	= "";
	
	for( var i = 0; i < cbx.length; i++ ){
		if( cbx[i].checked ){
			fvals += cbx[i].value + "_";
		}
	}
	//trim the last underscore
	fvals = fvals.substring( 0, fvals.length - 1 );
	
	var newURL;
	if( fvals != "" ){
		newURL = AddURLParameter( parameter, fvals );
	}else{
		newURL = RemoveURLParameter( parameter );
	}
	
	//ADD CPATH IF NEEDED
	if( cPath != "" && newURL.search("cPath") <= 0 ){
		newURL += "&cPath=" + cPath;
	}
	
	window.location = newURL;
}

function switch_check(id){
	/* ACTIVATES CHECKBOX SELECTOR
	 * ON FILTER LABEL CLICK.
	 *****************************/
	if( event.target.id != "cbxFilter_" + id ){
		if( $("#cbxFilter_" + id).is(':checked')){
			$("#cbxFilter_" + id).prop('checked',false);
			$("#lblFilter_" + id).removeClass("selectFilter");
			UpdateFilters();
		}else{
			$("#cbxFilter_" + id).prop('checked',true);
			$("#lblFilter_" + id).addClass("selectFilter");
			UpdateFilters();
		}
	}
}

function switch_other(parameter, value){
	/* ACTIVATES CHECKBOX SELECTOR ON 
	 * SPECIAL FILTER LABEL CLICK.
	 *********************************/
	var parameter_name = GetParameterFullName( parameter );
	if( event.target.id != "cbx_" + parameter_name + "_" + value ){
		if( $("#cbx_" + parameter_name + "_" + value).is(':checked')){
			$("#cbx_" + parameter_name + "_" + value).prop('checked',false);
			$("#lbl_" + parameter_name + "_" + value).removeClass("selectFilter");
			UpdateOther(parameter);
		}else{
			$("#cbx_" + parameter_name + "_" + value).prop('checked',true);
			$("#lbl_" + parameter_name + "_" + value).addClass("selectFilter");
			UpdateOther(parameter);
		}
	}
}

function GetParameterFullName( type ){
	/* TRANSLATES PARAMETER NAME
	 * INTO SPECIAL FILTER NAME
	 ***************************/
	var type_name = '';
	switch( type ){
		case 'm':
			type_name = 'manufacturers';
			break;
		case 'p':
			type_name = 'pricing';
			break;
		case 'r':
			type_name = 'ratings';
			break;
		case 's':
			type_name = 'specials';
			break;
		default:
			break;
	}
	return type_name;
}