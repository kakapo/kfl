function httpErrorReport(status){
	var message = "";
 	switch(status){
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
 	return message;
}
function clearTbody(_tbody){
	//var _tbody  = dojo.byId("database_edit_table");				
	for(var   i=_tbody.childNodes.length-1; i>= 0;i--)
	{
		_tbody.removeChild(_tbody.childNodes[i]);
	}
}
function fillTbody(_tbody,data){
	for(var i in data){
			
			var rowid= "row_"+data[i].name;
			var name= data[i].name;
			
			var row=document.createElement("tr");   
			row.id=rowid; 
				
			var firsttd = document.createElement("td");
			var textBox = new dijit.form.ValidationTextBox({readOnly:true,name:"name_"+name,value:data[i].name,style:{width:"150px"},required:true,regExp:"^[A-Za-z_]{1}[A-Za-z0-9_]*",invalidMessage:"变量名不符合规则:^[A-Za-z_]{1}[A-Za-z0-9_]*"});	
			firsttd.appendChild(textBox.domNode);
			row.appendChild(firsttd);
			
			var secondtd = document.createElement("td");	
			var textBox = new dijit.form.ValidationTextBox({name:"valu_"+name,value:data[i].value,required:true});
			secondtd.appendChild(textBox.domNode);
			row.appendChild(secondtd);
			
			var thirdtd = document.createElement("td");
			var delebutton = new dijit.form.Button({label:"删除",onClick:function(e){
				_tbody.removeChild(this.domNode.parentNode.parentNode);
			}});
			thirdtd.appendChild(delebutton.domNode);
			row.appendChild(thirdtd);
			
			_tbody.appendChild(row);      				
		}
}
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
    	        url: "/index.php",
    	        form: formid,
    	        handleAs: "json",
    	        load: function(data,ioargs){
    	          dojo.byId("AlertCon").innerHTML = data.m;
    	          dijit.byId("AlertShow").show();
    	        },
    	        error: function(error,ioargs){
    	          var message = httpErrorReport(ioargs.xhr.status);      	
    	          dojo.byId("AlertCon").innerHTML = message;
    	          dijit.byId("AlertShow").show();
    	        }
    	      }

     var deferred = dojo.xhrPost(xhrArgs);
     return false;
 }
function getText(pathinfo,title,params) {
	var _id = pathinfo.replace(/\//g,'_');
	var tabs = dijit.byId("maindiv");
	if(!dojo.byId(_id)){
		var pane = new dijit.layout.ContentPane({id:_id, title:title,closable:true });
		tabs.addChild(pane);
		tabs.selectChild(pane);
		pane.attr("onDownloadError", function(e){
			alert(e);
		});
		pane.attr("href", "/index.php"+pathinfo);

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

 function deleteDatabase(id,dbname){
 	
 	 dojo.byId('dblist').removeChild(dojo.byId(id));
 	 var xhrArgs = {
 			 	url: "/index.php",
 		        postData: "action=setting&op=deletedb&dbname="+dbname,
 	 	        handleAs: "json",
 	 	        load: function(data){ 		  
 	 	          dojo.byId("AlertCon2").innerHTML = data.m; 		  
 	 	          dijit.byId("AlertShow2").show();
 	 	        },
 	 	        error: function(error,ioargs){
 	 	          var message = httpErrorReport(ioargs.xhr.status); 
 	 	          dojo.byId("AlertCon2").innerHTML = message;
 	 	          dijit.byId("AlertShow2").show();
 	 	        }
 	 	      }
 	  
 	   	var deferred = dojo.xhrPost(xhrArgs);
 }

 function editDatabase(id,dbname){
 	dijit.byId('editDatabase').show();
 	var xhrArgs = {
 		      url: "/index.php/setting/getdb/"+dbname,
 		      handleAs: "json",
 		      preventCache: true,
 		     
 		      load: function(data, ioargs){
 					var _tbody = dojo.byId("database_edit_table");
 					
 					clearTbody(_tbody);
 					
 	      			dojo.byId("edit_dbname").value = dbname;
 	      			
 	      			fillTbody(_tbody,data);
					
 		        },
 		      error: function(error, ioargs){
 		          var message = httpErrorReport(ioargs.xhr.status);
 		          dojo.byId('dashboard').innerHTML = message;
 		        }
 		
 		    }
 		
 		    //Call the asynchronous xhrGet
 		    var deferred = dojo.xhrGet(xhrArgs);
 }


 function deleteMemcached(id,host){
 	 dojo.byId('memcachedlist').removeChild(dojo.byId(id));
 	 var xhrArgs = {
 			 	url: "/index.php",
 		        postData: "action=cache&op=deletememcached&host="+host,
 	 	        handleAs: "json",
 	 	        load: function(data){
 	 	          dojo.byId("AlertCon2").innerHTML = data.m;
 	 	          dijit.byId("AlertShow2").show();
 	 	        },
 	 	        error: function(error,ioargs){
 	 	          var message = httpErrorReport(ioargs.xhr.status);
 	 	          dojo.byId("AlertCon2").innerHTML = message;
 	 	          dijit.byId("AlertShow2").show();
 	 	        }
 	 	      }
 	  
 	   	var deferred = dojo.xhrPost(xhrArgs);
 }

 function editMemcached(id,host){
 	dijit.byId('editMemcached').show();
 	var xhrArgs = {
 		      url: "/index.php/cache/getmemcached/"+host,
 		      handleAs: "json",
 		      preventCache: true,
 		     
 		      load: function(data, ioargs){
 					var _tbody = dojo.byId("memcached_edit_table");
 					clearTbody(_tbody);
 					
 	      			dojo.byId("edit_host").value = host;
 	      			
 	      			fillTbody(_tbody,data);
 					
 		        },
 		      error: function(error, ioargs){
 		          var message = httpErrorReport(ioargs.xhr.status);
 		          dojo.byId('dashboard').innerHTML = message;
 		        }
 		
 		    }
 		
 		    //Call the asynchronous xhrGet
 		    var deferred = dojo.xhrGet(xhrArgs);
 }

 function view_memcached(host){
 	
 	var tabs = dijit.byId("maindiv");

 	data = '<iframe src="/plugins/memcache.php?host='+host+'" width="100%" frameborder="0" height="700px"></iframe>';
 	var pane = new dijit.layout.ContentPane({id:host, title:host,closable:true, content:data });
 	tabs.addChild(pane);
 	tabs.selectChild(pane);
 }
 function view_phpinfo(id){
	 var tabs = dijit.byId("maindiv");

 	data = '<iframe src="/index.php/index/phpinfo" width="100%" frameborder="0" height="700px"></iframe>';
 	var pane = new dijit.layout.ContentPane({id:id, title:id,closable:true, content:data });
 	tabs.addChild(pane);
 	tabs.selectChild(pane);
 }

 function deletePageRule(id,page_rule){
 	 dojo.byId('pagerulelist').removeChild(dojo.byId(id));
 	 var xhrArgs = {
 			 	url: "/index.php",
 		        postData: "action=cache&op=deletepagerule&pagerule="+page_rule,
 	 	        handleAs: "json",
 	 	        load: function(data){
 	 	 		  
 	 	          dojo.byId("AlertCon2").innerHTML = data.m;
 	 	 		  
 	 	          dijit.byId("AlertShow2").show();
 	 	        },
 	 	        error: function(error,ioargs){
 	 	          var message = httpErrorReport(ioargs.xhr.status);
 	 	          dojo.byId("AlertCon2").innerHTML = message;
 	 	          dijit.byId("AlertShow2").show();
 	 	        }
 	 	      }
 	  
 	   	var deferred = dojo.xhrPost(xhrArgs);
 }

 function editPageRule(id,pagerule){
 	dijit.byId('editPageRule').show();
 	var xhrArgs = {
 		      url: "/index.php/cache/getpagerule/"+pagerule,
 		      handleAs: "json",
 		      preventCache: true,	     
 		      load: function(data, ioargs){
 					var _tbody = dojo.byId("page_edit_table");
 					clearTbody(_tbody);		 		
 	      			dojo.byId("edit_pagerule").value = pagerule;     			
 	      			fillTbody(_tbody,data);				
 		        },
 		      error: function(error, ioargs){
 		          var message = httpErrorReport(ioargs.xhr.status);
 		          dojo.byId('dashboard').innerHTML = message;
 	        	}	
 		    }
 		
 		    //Call the asynchronous xhrGet
 		    var deferred = dojo.xhrGet(xhrArgs);
 }
 function viewSessionValue(key,server){
	 var xhrArgs = {
			 	url: "/index.php/session/viewsession",
			 	handleAs: "text",
			 	content: {
	                key: key,
	                server: server
	            },
			 	preventCache: true, 	     
	 	        load: function(data){ 	 		  
	 	          dojo.byId("sessionValue").innerHTML = data; 	 		  
	 	          dijit.byId("sessionDialog").show();
	 	        },
	 	        error: function(error,ioargs){
	 	          var message = httpErrorReport(ioargs.xhr.status);
	 	          dojo.byId("AlertCon2").innerHTML = message;
	 	          dijit.byId("AlertShow2").show();
	 	        }
	 	      }
	  
	   	var deferred = dojo.xhrGet(xhrArgs);
	 
 }
 function deleteErrorLog(error_no){
 	 var xhrArgs = {
		 	url: "/index.php",
	        postData: "action=monitor&op=delerrorlog&error_no="+error_no,
 	        handleAs: "json",
 	        load: function(data){ 
 	          dojo.byId("AlertCon2").innerHTML = data.m; 		  
 	          dijit.byId("AlertShow2").show();
 	          dijit.byId("_monitor_errorlog").refresh();
 	        },
 	        error: function(error,ioargs){
 	          var message = httpErrorReport(ioargs.xhr.status);
 	          dojo.byId("AlertCon2").innerHTML = message;
 	          dijit.byId("AlertShow2").show();
 	        }
 	      }
  
   	var deferred = dojo.xhrPost(xhrArgs);
 }
 function viewErrorLog(error_no){
 	 var xhrArgs = {
			 	url: "/index.php/monitor/viewerrorlog",
			 	handleAs: "text",
			 	content: {
	                error_no: error_no
	            },
			 	preventCache: true, 	     
	 	        load: function(data){ 	 		  
	 	          dojo.byId("errorLogValue").innerHTML = data; 	 		  
	 	          dijit.byId("errorLogDialog").show();
	 	        },
	 	        error: function(error,ioargs){
	 	        	
	 	          var message = httpErrorReport(ioargs.xhr.status);
	 	          dojo.byId("AlertCon2").innerHTML = message;
	 	          dijit.byId("AlertShow2").show();
	 	        }
	 	      }
	  
	   	var deferred = dojo.xhrGet(xhrArgs);
 }
 function gotopage(page){ 
	 dijit.byId("_monitor_errorlog").attr("href","/index.php/monitor/errorlog/page/"+page);
 }
 function renewConfigFile(op){
 	 var xhrArgs = {
 		 	url: "/index.php",
 	        postData: "action=index&op="+op,
  	        handleAs: "json",
  	        load: function(data){ 
 		// alert(data);
  	          dojo.byId("AlertCon2").innerHTML = data.m; 		  
  	          dijit.byId("AlertShow2").show();
  	          
  	        },
  	        error: function(error,ioargs){
  	          var message = httpErrorReport(ioargs.xhr.status);
  	          dojo.byId("AlertCon2").innerHTML = message;
  	          dijit.byId("AlertShow2").show();
  	        }
  	      }
 
    var deferred = dojo.xhrPost(xhrArgs);
 }
 function restoreConfigFile(){
 	var xhrArgs = {
 		 	url: "/plugins/restoreConfigFile.php",
  	        handleAs: "json",
  	        load: function(data){ 
 		
  	          dojo.byId("AlertCon2").innerHTML = data.m; 		  
  	          dijit.byId("AlertShow2").show();
  	          
  	        },
  	        error: function(error,ioargs){
  	          var message = httpErrorReport(ioargs.xhr.status);
  	          dojo.byId("AlertCon2").innerHTML = message;
  	          dijit.byId("AlertShow2").show();
  	        }
  	      }
 
    var deferred = dojo.xhrPost(xhrArgs);
 
 }