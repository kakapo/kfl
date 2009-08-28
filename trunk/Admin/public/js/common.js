
 function in_array(needle, haystack) {
	  if(typeof needle == 'string' || typeof needle == 'number') {
	    for(var i in haystack) {
	      if(haystack[i] == needle) {
	          return true;
	      }
	    }
	  }
	  return false;
}
 function submitForm(formid){
	     
     var xhrArgs = {
    	        form: dojo.byId(formid),
    	        handleAs: "json",
    	        load: function(data){
    	 		  
    	          dojo.byId("AlertCon").innerHTML = data.m;
    	          dijit.byId("AlertShow").show();
    	        },
    	        error: function(error,ioargs){
    	        	var message = "";
    	        	switch(ioargs.xhr.status){
	   	             case 404:
	   	               message = "访问的页面不存在！";
	   	               break;
	   	             case 500:
	   	               message = "服务器内部错误！";
	   	               break;
	   	             case 407:
	   	               message = "You need to authenticate with a proxy.";
	   	               break;
	   	             default:
	   	               message = "未知错误.";
	   	          }
    	          dojo.byId("AlertCon").innerHTML = message;
    	          dijit.byId("AlertShow").show();
    	        }
    	      }
      //Call the asynchronous xhrPost
      //dojo.byId("AlertShow").innerHTML = "Form being sent..."
      var deferred = dojo.xhrPost(xhrArgs);
     
	
     return false;
 }
function getText(pathinfo,title,params) {
	var _id = pathinfo.replace(/\//g,'_');
	var tabs = dijit.byId("maindiv");
	if(!dojo.byId(_id)){

		var xhrArgs = {
	      url: "/index.php"+pathinfo,
	      handleAs: "text",
	      preventCache: true,
	      content: params,
	      load: function(data, ioargs){
				
				var pane = new dijit.layout.ContentPane({id:_id, title:title,closable:true, content:data });
				tabs.addChild(pane);
				tabs.selectChild(pane);
				
	        },
	      error: function(error, ioargs){
	          var message = "";
	          
	          switch(ioargs.xhr.status){
	             case 404:
	               message = "访问的页面不存在！";
	               break;
	             case 500:
	               message = "服务器内部错误！";
	               break;
	             case 407:
	               message = "You need to authenticate with a proxy.";
	               break;
	             default:
	               message = "未知错误.";
	          }
	          dojo.byId('dashboard').innerHTML = message;
	        }
	
	    }
	
	    //Call the asynchronous xhrGet
	    var deferred = dojo.xhrGet(xhrArgs);
	}
  }
 function aboutus(){
	 dijit.byId("dialog1").show();
 }
 function deleteRow(table,id){
	 
	 if(dojo.byId(id)){
		 dojo.byId(table).removeChild(dojo.byId(id));
	 }
	 //dojo.byId(id).style.hidden = true;
 }
 function addRow(table){
	var d= new Date();
	var rowid= table+d.getMilliseconds().toString();
	
	var row=document.createElement("tr");   
	row.id=rowid; 
		
	var firsttd = document.createElement("td");
	var textBox = new dijit.form.ValidationTextBox({name:"name_"+rowid,style:{width:"150px"},required:true,regExp:"^[A-Za-z_]{1}[A-Za-z0-9_]*",invalidMessage:"变量名不符合规则:^[A-Za-z_]{1}[A-Za-z0-9_]*"});	
	firsttd.appendChild(textBox.domNode);
	row.appendChild(firsttd);
	
	var secondtd = document.createElement("td");
	
	var textBox = new dijit.form.TextBox({name:"valu_"+rowid});
	secondtd.appendChild(textBox.domNode);
	row.appendChild(secondtd);
	
	var thirdtd = document.createElement("td");
	var button =new dijit.form.Button({label:"删除",onClick:function(){deleteRow(table,rowid)}});
	thirdtd.appendChild(button.domNode);
	row.appendChild(thirdtd);
	
	dojo.byId(table).appendChild(row);   
	
	
 }