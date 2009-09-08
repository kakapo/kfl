 function doPost(url,postdata,form){
 	 var xhrArgs = {
 		 	url: url,
  	        handleAs: "text",
  	        load: function(data){ 
  	        	alert(data);
  	          dojo.byId("AlertCon2").innerHTML = data.m; 		  
  	          dijit.byId("AlertShow2").show();      
  	        },
  	        error: function(error,ioargs){
  	        	alert(error);
  	          var message = httpErrorReport(ioargs.xhr.status);
  	          dojo.byId("AlertCon2").innerHTML = message;
  	          dijit.byId("AlertShow2").show();
  	        }
  	      }
 	if(postdata) xhrArgs.postData = postdata;
 	if(form) xhrArgs.form = form;
    var deferred = dojo.xhrPost(xhrArgs);
 }
 function doGet(url,content,loadCallBack){
	var xhrArgs = {
		      url: url,
		      handleAs: "json",
		      preventCache: true,     
		      load: loadCallBack,
		      error: function(error, ioargs){
		          var message = httpErrorReport(ioargs.xhr.status);
		          dojo.byId("AlertCon2").innerHTML = message;
 	          dijit.byId("AlertShow2").show();
		        }	
		    }		
    if(content) xhrArgs.content=content;
    var deferred = dojo.xhrGet(xhrArgs);
}
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
 	 doPost("/index.php","action=setting&op=deletedb&dbname="+dbname);
 }

 function editDatabase(id,dbname){
 	dijit.byId('editDatabase').show();
 	doGet("/index.php/setting/getdb/"+dbname,'',function(data, ioargs){
 					var _tbody = dojo.byId("database_edit_table");			
 					clearTbody(_tbody);					
 	      			dojo.byId("edit_dbname").value = dbname;	      			
 	      			fillTbody(_tbody,data);					
 		        });
 }

 function deleteMemcached(id,host){
 	 dojo.byId('memcachedlist').removeChild(dojo.byId(id));
 	 doPost("/index.php","action=cache&op=deletememcached&host="+host);
 }

 function editMemcached(id,host){
 	dijit.byId('editMemcached').show();
 	doGet("/index.php/cache/getmemcached/"+host,'',function(data, ioargs){
 					var _tbody = dojo.byId("memcached_edit_table");
 					clearTbody(_tbody);					
 	      			dojo.byId("edit_host").value = host;      			
 	      			fillTbody(_tbody,data);					
 		        });
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
 	 doPost("/index.php","action=cache&op=deletepagerule&pagerule="+page_rule);
 }

 function editPageRule(id,pagerule){
 	dijit.byId('editPageRule').show();
 	doGet("/index.php/cache/getpagerule/"+pagerule,'',function(data, ioargs){
 					var _tbody = dojo.byId("page_edit_table");
 					clearTbody(_tbody);		 		
 	      			dojo.byId("edit_pagerule").value = pagerule;     			
 	      			fillTbody(_tbody,data);				
 		        });
 }
 function viewSessionValue(key,server){
 	doGet("/index.php/session/viewsession",{
	                key: key,
	                server: server
	            },function(data){ 	 		  
	 	          dojo.byId("sessionValue").innerHTML = data; 	 		  
	 	          dijit.byId("sessionDialog").show();
	 	        });
	 
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
	doGet("/index.php/monitor/viewerrorlog",'',function(data){ 	 		  
          dojo.byId("errorLogValue").innerHTML = data; 	 		  
          dijit.byId("errorLogDialog").show();
        });
 }
 function gotopage(page){ 
	 dijit.byId("_monitor_errorlog").attr("href","/index.php/monitor/errorlog/page/"+page);
 }
 function renewConfigFile(op){
 	doPost("/index.php","action=project&op="+op+"&app_name="+gCurAppName);
 }

 function restoreConfigFile(){
 	doPost("/plugins/restore.php","app_name="+gCurAppName);
 }
 function refreshTree(){
	 var app_name = gCurAppName;
	 var app_dir = gCurAppDir;	
	if(dijit.byId("file_tree")){
	 	dijit.byId("file_tree").destroy();//Enter the tree widget ID
	 }

	 var store = new dojo.data.ItemFileReadStore({
	      url: "/index.php/project/appdir/app_name/"+app_name, urlPreventCache:"true",jsId:"dirStore"
	  });
	//Fetch the data.
	//test 
	store.fetch({
         query: {
             'dir': app_dir
         },  
         onComplete: function(items, request){
        	 //alert(items.length);
         },
         onError: function (error, request) {
             alert("lookup failed.");
             alert(error);
         },
         queryOptions: {
             deep: true
         }
     });

     var treeModel = new dijit.tree.ForestStoreModel({
          store: store,
          query: {
	     	         "dir": app_dir
	     	      },
          rootId: "root",
          rootLabel: app_name,
          childrenAttrs: ["folders"]
      });

     var tree= new dijit.Tree({
    	  id:"file_tree",
          model: treeModel,
          showRoot: true,
          openOnClick:"true"
      },
      "file_tree");
     var block = document.getElementById('target');
     block.appendChild(tree.domNode);
     tree.startup();
     //tree.connect(tree, "onClick", function(item){	      	    	  
  	//  alert(store.getLabel(item));
     //	});
     var menu = dijit.byId("tree_menu");
      // when we right-click anywhere on the tree, make sure we open the menu
      menu.bindDomNode(tree.domNode);

      dojo.connect(menu, "_openMyself", tree, function(e) {
          // get a hold of, and log out, the tree node that was the source of this open event
          var tn = dijit.getEnclosingWidget(e.target);
          //console.debug(tn);

          // now inspect the data store item that backs the tree node:
         // alert(tn.item);

          // contrived condition: if this tree node doesn't have any children, disable all of the menu items
          menu.getChildren().forEach(function(i) {
              i.attr('disabled', !tn.item.children);
          });

          // IMPLEMENT CUSTOM MENU BEHAVIOR HERE
      });
 }
 function openProject(){
	 gCurAppName = dijit.byId('projectSelect').attr('value');
	 
	 projectStore.fetch({
         query: {
             'app_name': gCurAppName
         },  
         onComplete: function(items, request){
        	 gCurAppDir = items[0].app_dir.toString();
         },
         onError: function (error, request) {
             alert("lookup failed.");
             alert(error);
         },
         queryOptions: {
             deep: true
         }
     });
	 
	 refreshTree(gCurAppDir);
 }
 function deleteProject(){
 	raiseQueryDialog("确认要删除吗？", "一旦确认删除，所有目录和文件将不复存在！请慎重考虑？！<br>", function(arg){
 			
 		doPost("/index.php","action=project&op=deleteapp&app_name="+gCurAppName);
 		
 	});
 }
 function raiseQueryDialog(title, question, callbackFn) {

        var errorDialog = new dijit.Dialog({ id: 'queryDialog', title: title });
        // When either button is pressed, kill the dialog and call the callbackFn.
        var commonCallback = function(mouseEvent) {
        errorDialog.hide();
        errorDialog.destroyRecursive();
                if (mouseEvent.explicitOriginalTarget.id == 'yesButton') {
                        callbackFn(true);
                } else {
                        callbackFn(false);
                }
        };
                var questionDiv = dojo.create('div', { innerHTML: question });
        var yesButton = new dijit.form.Button(
                    { label: '确认', id: 'yesButton', onClick: commonCallback });
        var noButton = new dijit.form.Button(
                    { label: '取消', id: 'noButton', onClick: function(){dijit.byId("queryDialog").destroyRecursive();} });

        errorDialog.containerNode.appendChild(questionDiv);
        errorDialog.containerNode.appendChild(yesButton.domNode);
        errorDialog.containerNode.appendChild(noButton.domNode);
        errorDialog.show();
}