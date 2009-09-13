var selected_copy_file='';

function myAlert(text){
	dojo.byId("AlertCon2").innerHTML = text; 		  
    dijit.byId("AlertShow2").show();   
}
function doPost(url,postdata,form,callback){
	 
 	 var xhrArgs = {
 		 	url: url,
  	        handleAs: "json",
  	        load: function(data){ 
  	        	//alert(data);
  	        	myAlert(data.m);
   
  	        },
  	        error: function(error,ioargs){
  	          alert(error);
  	          var message = httpErrorReport(ioargs.xhr.status);
  	          myAlert(message);
  	        }
  	      }
 	if(postdata) xhrArgs.postData = postdata;
 	if(form) xhrArgs.form = form;
 	if(callback) xhrArgs.load = callback;
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
		          myAlert(message);
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
			
			//var thirdtd = document.createElement("td");
			//var delebutton = new dijit.form.Button({label:"删除",onClick:function(e){
			//	_tbody.removeChild(this.domNode.parentNode.parentNode);
			//}});
			//thirdtd.appendChild(delebutton.domNode);
			//row.appendChild(thirdtd);
			
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
    	        url: gSiteUrl+"/index.php",
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
function getText(pathinfo,title) {
	var _id = pathinfo.replace(/\//g,'_');
	if(!dojo.byId(_id)){
		var pane = new dijit.layout.ContentPane({id:_id, title:title,closable:true });
		var tabs = dijit.byId("maindiv");
		tabs.addChild(pane);
		tabs.selectChild(pane);
		pane.attr("onDownloadError", function(e){
			alert(e);
		});
		pane.attr("href", gSiteUrl+"/index.php"+pathinfo);

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
 	 doPost(gSiteUrl+"/index.php","action=setting&op=deletedb&dbname="+dbname);
 }

 function editDatabase(id,dbname){
 	dijit.byId('editDatabase').show();
 	doGet(gSiteUrl+"/index.php/setting/getdb/"+dbname,'',function(data, ioargs){
 					var _tbody = dojo.byId("database_edit_table");			
 					clearTbody(_tbody);					
 	      			dojo.byId("edit_dbname").value = dbname;	      			
 	      			fillTbody(_tbody,data);					
 		        });
 }

 function deleteMemcached(id,host){
 	 dojo.byId('memcachedlist').removeChild(dojo.byId(id));
 	 doPost(gSiteUrl+"/index.php","action=cache&op=deletememcached&host="+host);
 }

 function editMemcached(id,host){
 	dijit.byId('editMemcached').show();
 	doGet(gSiteUrl+"/index.php/cache/getmemcached/"+host,'',function(data, ioargs){
 					var _tbody = dojo.byId("memcached_edit_table");
 					clearTbody(_tbody);					
 	      			dojo.byId("edit_host").value = host;      			
 	      			fillTbody(_tbody,data);					
 		        });
 }

 function view_memcached(host){
 	var tabs = dijit.byId("maindiv");
 	data = '<iframe src="'+gSiteUrl+'/plugins/memcache.php?host='+host+'" width="100%" frameborder="0" height="700px"></iframe>';
 	var pane = new dijit.layout.ContentPane({id:host, title:host,closable:true, content:data });
 	tabs.addChild(pane);
 	tabs.selectChild(pane);
 }
 function view_phpinfo(id){
	 var tabs = dijit.byId("maindiv");

 	data = '<iframe src="'+gSiteUrl+'/index.php/index/phpinfo" width="100%" frameborder="0" height="700px"></iframe>';
 	var pane = new dijit.layout.ContentPane({id:id, title:id,closable:true, content:data });
 	tabs.addChild(pane);
 	tabs.selectChild(pane);
 }

 function deletePageRule(id,page_rule){
 	 dojo.byId('pagerulelist').removeChild(dojo.byId(id));
 	 doPost(gSiteUrl+"/index.php","action=cache&op=deletepagerule&pagerule="+page_rule);
 }

 function editPageRule(id,pagerule){
 	dijit.byId('editPageRule').show();
 	doGet(gSiteUrl+"/index.php/cache/getpagerule/"+pagerule,'',function(data, ioargs){
 					var _tbody = dojo.byId("page_edit_table");
 					clearTbody(_tbody);		 		
 	      			dojo.byId("edit_pagerule").value = pagerule;     			
 	      			fillTbody(_tbody,data);				
 		        });
 }
 function viewSessionValue(key,server){
	 var xhrArgs = {
		      url: gSiteUrl+"/index.php/session/viewsession",
		      handleAs: "text",
		      preventCache: true,     
		      content:{
	                key: key,
	                server: server
	            },
		      load: function(data){
	            	
		 	          dojo.byId("sessionValue").innerHTML = data; 	 		  
		 	          dijit.byId("sessionDialog").show();
		 	        },
		      error: function(error, ioargs){
		 	        	alert(error);
		          var message = httpErrorReport(ioargs.xhr.status);
		          myAlert(message);
		        }	
		    }		
  
   var deferred = dojo.xhrGet(xhrArgs);
   
	 
 }
 function deleteErrorLog(error_no){	
 	 var xhrArgs = {
		 	url: gSiteUrl+"/index.php",
	        postData: "action=monitor&op=delerrorlog&error_no="+error_no,
 	        handleAs: "json",
 	        load: function(data){ 
 		 	  myAlert(data.m);
 	          dijit.byId("_monitor_errorlog").refresh();
 	        },
 	        error: function(error,ioargs){
 	          var message = httpErrorReport(ioargs.xhr.status);
 	          myAlert(message);

 	        }
 	      }
  
   	var deferred = dojo.xhrPost(xhrArgs);
 }
 function viewErrorLog(error_no){
	 var xhrArgs = {
		      url: gSiteUrl+"/index.php/monitor/viewerrorlog",
		      handleAs: "text",
		      content:{error_no: error_no},
		      preventCache: true,     
		      load: function(data){ 	 		  
		         dojo.byId("errorLogValue").innerHTML = data; 	 		  
		         dijit.byId("errorLogDialog").show();
		       },
		      error: function(error, ioargs){
		          var message = httpErrorReport(ioargs.xhr.status);
		          myAlert(message);
		        }	
		    }		
  
   var deferred = dojo.xhrGet(xhrArgs);

 }
 function gotopage(page){ 
	 dijit.byId("_monitor_errorlog").attr("href",gSiteUrl+"/index.php/monitor/errorlog/page/"+page);
 }
 function renewConfigFile(op){
	 if(gCurAppName=='') return myAlert('请选择项目');
 	doPost(gSiteUrl+"/index.php","action=project&op="+op+"&app_name="+gCurAppName);
 }

 function restoreConfigFile(){
	 if(gCurAppName=='') return myAlert('请选择项目');
 	doPost("/plugins/restore.php","app_name="+gCurAppName);
 }
 function refreshTree(){
	 var app_name = gCurAppName;
	 var app_dir = gCurAppDir;	
	// alert('gCurAppName：'+gCurAppName+" len:"+gCurAppName.length);
	// alert('gCurAppDir:'+gCurAppDir+" len:"+gCurAppDir.length);
	 if(dijit.byId("file_tree")){
		 dijit.byId("tree_menu").unBindDomNode(dijit.byId("file_tree").domNode);
		 dojo.disconnect(handle);

	 	 dijit.byId("file_tree").destroyRecursive();//Enter the tree widget ID	
	 }

	 treeStore = new dojo.data.ItemFileReadStore({
	      url: gSiteUrl+"/index.php/project/appdir/app_name/"+app_name, urlPreventCache:"true",jsId:"dirStore"
	  });
	//Fetch the data.
	//test 
	 treeStore.fetch({
         query: {
             'dir': app_dir
         },  
         onComplete: function(items, request){
        	 //alert(items.length);
         },
         onError: function (error, request) {
             alert("项目目录不存在!");
             //alert(error);
             
         },
         queryOptions: {
             deep: true
         }
     });

     var treeModel = new dijit.tree.ForestStoreModel({
          store: treeStore,
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
     tree.connect(tree, "onClick", function(item){	      	    	  
    	 var _id = item.id.toString();//treeStore.getValue(item, "id");
    	 var title = item.name.toString();//treeStore.getValue(item, "name");
    	 var path = item.path.toString();//treeStore.getValue(item, "path");
    	 viewFile(_id,title,path);
  	  
     });
     
     var menu = dijit.byId("tree_menu");
      // when we right-click anywhere on the tree, make sure we open the menu
      menu.bindDomNode(dijit.byId('file_tree').domNode);

      var handle = dojo.connect(menu, "_openMyself", dijit.byId('file_tree'), function(e) {
          // get a hold of, and log out, the tree node that was the source of this open event
          var tn = dijit.getEnclosingWidget(e.target);
          //console.debug(tn);
          
          // now inspect the data store item that backs the tree node:
         // console.debug(tn.item.id);
         

          // contrived condition: if this tree node doesn't have any children, disable all of the menu items
          menu.getChildren().forEach(function(i) {
          	// 
          	 if(tn.item.id=='root' ){
             if( i.label=='打开' ||  i.label=='复制' || i.label=='删除' ||  i.label=='下载'||  i.label=='重命名' || i.label=='属性') i.attr('disabled', true);
             	if(i.label=='新建文件夹' || i.label=='上传文件') {            		
             		i.attr('disabled', false);
             	}
             }
             if(tn.item.id!='root' ){
             	if(  i.label=='打开' ||  i.label=='复制' || i.label=='删除' ||  i.label=='下载'||  i.label=='重命名' || i.label=='属性') i.attr('disabled', false);
             }
             if(tn.item.filetype=='file'){
             	if(i.label=='新建文件夹' || i.label=='上传文件') {            		
             		i.attr('disabled', true);
             	}
             	if(i.label=='下载'){
             		i.attr('disabled', false);
             	}
             }
             if(tn.item.filetype=='dir'){
             	if(i.label=='新建文件夹' || i.label=='上传文件') {
             		i.attr('disabled', false);
             	}
             	if(i.label=='下载'){
             		i.attr('disabled', true);
             	}
             	
             }
             if(i.label=='粘帖'){
             	i.attr('disabled', true);
             }
             
             if(selected_copy_file!='' && i.label=='粘帖' && (tn.item.filetype=='dir' || tn.item.id=='root')){
             	i.attr('disabled', false);
             }
             
             i.attr('title', tn.item.id.toString());
           
          });
          
          // IMPLEMENT CUSTOM MENU BEHAVIOR HERE
      });
 }

 
 
 function newProject(){
	 doPost(gSiteUrl+"/index.php",'','appcreate_form',function(data){ 
		myAlert(data.m);
		
		if(data.s==200){
			gCurAppName = dojo.byId('app_name').value;
			loadProject(gCurAppName);
			openProject();
		}
	});
 }
 function openProject(){
	 gCurAppName = dijit.byId('projectSelect').attr('value');
	 if(gCurAppName=='') return myAlert('请选择项目');
	 projectStore.fetch({
         query: {
             'app_name': gCurAppName
         },  
         onComplete: function(items, request){
        	 gCurAppDir = items[0].app_dir.toString();
        	
        	 dijit.byId("leftDiv").selectChild(dijit.byId("explorerApp"));
        	 refreshTree();
         },
         onError: function (error, request) {
             alert("lookup failed.");
             alert(error);
         },
         queryOptions: {
             deep: true
         }
     });
	
	 
 }
 function deleteProject(){
	 var toDeleteApp = dijit.byId('projectSelect').attr('value');
	if(toDeleteApp=='') return myAlert('请选择项目');
 	raiseQueryDialog("确认要删除吗？", "一旦确认删除，所有目录和文件将不复存在！请慎重考虑？！<br>", function(arg){
 		
 		doPost(gSiteUrl+"/index.php","action=project&op=deleteapp&app_name="+toDeleteApp,'',function(data){ 
 				myAlert(data.m);
				if(toDeleteApp==gCurAppName) gCurAppName = '';
				loadProject(gCurAppName);
				if(dijit.byId("file_tree")){
				 	dijit.byId("file_tree").destroyRecursive();//Enter the tree widget ID
				}
		});
 		
 	});
 }
 function raiseQueryDialog(title, question, callbackFn) {

        var errorDialog = new dijit.Dialog({ id: 'queryDialog', title: title });
        
        var questionDiv = dojo.create('div', { innerHTML: question });
        var yesButton = new dijit.form.Button(
                    { label: '确认', id: 'yesButton', onClick: function(){
                    	errorDialog.hide();
                    	errorDialog.destroyRecursive();
                    	callbackFn(true) } });
        var noButton = new dijit.form.Button(
                    { label: '取消', id: 'noButton', onClick: function(){dijit.byId("queryDialog").destroyRecursive();} });

        errorDialog.containerNode.appendChild(questionDiv);
        errorDialog.containerNode.appendChild(yesButton.domNode);
        errorDialog.containerNode.appendChild(noButton.domNode);
        errorDialog.show();
}
 function loadProject(default_value){
	 
	 if(dijit.byId("projectSelect")){
		 dijit.byId("projectSelect").destroyRecursive();//Enter the tree widget ID
	 }
	 projectStore = new dojo.data.ItemFileReadStore({
         url: gSiteUrl+"/index.php/project/getallapp",
         preventCache: true
     });

     var projectSelect = new dijit.form.ComboBox({
         id: "projectSelect",
         name: "state",
         value: default_value,
         store: projectStore,
         searchAttr: "app_name"
     },
     "projectSelect");

     var projectContainer = document.getElementById('projectContainer');
     projectContainer.appendChild(projectSelect.domNode);
     
     projectSelect.startup();
 }
 
 function searchErrorLog(){
 	dijit.byId("_monitor_errorlog").attr("href",gSiteUrl+"/index.php/monitor/errorlog/error_no/"+dojo.byId("error_no").value)
 }
 function visitApp(hosturl){
 	if(gCurAppName=='') return myAlert('请选择项目');
 	window.open(hosturl+"/"+gCurAppName);
 }
 function getItemById(id){
 	//alert(typeof(id)+id.length);
 	var item=[];
 	if(id=='root') {
 		item['path'] = gCurAppDir;
 		return item;
 	}
	treeStore.fetch({
	     query: {
	         'id': id
	     },  
	     onComplete: function(items, request){
			item = items[0];
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
	
	return item;
 }
 function viewFile(id,title,path){
	if(!title && !path){
		item = getItemById(id);
		title = item['name'];
	    path = item['path'];	 
	}
	 if(!dojo.byId(id)){
			var pane = new dijit.layout.ContentPane({id:id, title:title,closable:true });
			var tabs = dijit.byId("maindiv");
			tabs.addChild(pane);
			tabs.selectChild(pane);
			pane.attr("onDownloadError", function(e){
				alert(e);
			});
			pane.attr("href", gSiteUrl+"/index.php/project/dumpfile/"+path);

	}
 }
 function fileInfo(id){
 	item = getItemById(id);
 	var info='';
	 info += '位置:'+unescape(item['dir'])+"<br>";
	 info += '大小:'+item['size']+"<br>";
	 info += '最后修改:'+item['time']; 
	dojo.byId("AlertCon3").innerHTML = info; 		  
	dijit.byId("AlertShow3").show();   
 }
  function downloadFile(id){
  	item = getItemById(id);
  	window.open(gSiteUrl+"/index.php/project/download/path/"+item['path']);
  }
  function deleteFile(id){
  	item = getItemById(id);
 	raiseQueryDialog("确认要删除吗？", "是否确定要删除'"+item['name']+"'？所有数据将丢失并且无法恢复。<br>", 		function(arg){
 		
	 	doPost(gSiteUrl+"/index.php","action=project&op=deletefile&file="+item['path'],'',function(data)	{ 
 				myAlert(data.m);
				if(data.s==200) refreshTree();
		});
 		
 	});
 }
 
 function copyFile(id){
 	item = getItemById(id);
 	selected_copy_file = item['path'];
 }
 function pasteFile(id){
 	item = getItemById(id);
 	doPost(gSiteUrl+"/index.php","action=project&op=pastefile&file="+selected_copy_file+"&todir="+item['path'],'',function(data)	{ 
 				myAlert(data.m);
				if(data.s==200) refreshTree();
	});
 }
 function newFolder(){
 	
 	var id = dojo.byId('under_which_dir').value;
 	var newfolder = dojo.byId('newfolder').value;
 	var reCat = /^[A-Za-z_]{1}[A-Za-z0-9_]*/gi;
    if(!reCat.test(newfolder)) {
    	return;
    }
 	item = getItemById(id);
 	doPost(gSiteUrl+"/index.php","action=project&op=newfolder&id="+id+"&newfolder="+newfolder+"&todir="+item['path'],'',function(data){ 
 		
 				myAlert(data.m);
				if(data.s==200) 
				{
					refreshTree();
					dojo.byId('newfolder').value='';
					dijit.byId('AlertShow4').hide();
				}
	});
 }
 function renameFile(){
 	var id = dojo.byId('old_file_id').value;
 	var newfilename = dojo.byId('newfilename').value;
 	var reCat = /^[A-Za-z_]{1}[A-Za-z0-9_\.]*/gi;
    if(!reCat.test(newfilename)) {
    	return;
    }
 	item = getItemById(id);
 	doPost(gSiteUrl+"/index.php","action=project&op=renamefile&newfilename="+newfilename+"&oldfile="+item['path'],'',function(data){ 
 		
 				myAlert(data.m);
				if(data.s==200) 
				{
					refreshTree();
					dojo.byId('newfilename').value='';
					dijit.byId('AlertShow5').hide();
				}
	});
 }
var uploadUrl = gSiteUrl+"/index.php";
var rmFiles = "";
var fileMask = [
	["Php File", 	"*.php"],
	["Js File", 	"*.js"],
	["Css File", 	"*.css"],
	["HTML File", 	"*.html"],
	["Jpeg File", 	"*.jpg;*.jpeg"],
	["GIF File", 	"*.gif"],
	["PNG File", 	"*.png"],
	["All Images", 	"*.jpg;*.jpeg;*.gif;*.png"]
];
function prepareUpload(id){
	dijit.byId('AlertShow6').show();
	item = getItemById(id);
	var f0 = new dojox.form.FileUploader({
		button:dijit.byId("btn0"), 
		degradable:false,
		uploadUrl:uploadUrl, 
		uploadOnChange:false, 
		selectMultipleFiles:false,
		fileMask:fileMask,
		isDebug:false,
		postData:{action:"project", op:"uploadfile",path:item['path']}

	});
	
	dojo.connect(f0, "onChange", function(data){
		//console.log("DATA:", data);
		dojo.forEach(data, function(d){
			//file.type no workie from flash selection (Mac?)
			dojo.byId("fileToUpload").value = d.name+" "+Math.ceil(d.size*.001)+"kb \n";
		});
	});

	dojo.connect(f0, "onProgress", function(data){
		//console.warn("onProgress", data);
		dojo.byId("fileToUpload").value = "";
		dojo.forEach(data, function(d){
			dojo.byId("fileToUpload").value += "("+d.percent+"%) "+d.name+" \n";
			
		});
	});

	dojo.connect(f0, "onComplete", function(data){
		//console.warn("onComplete", data);
		refreshTree();
		dojo.byId("fileToUpload").value = '';
		dijit.byId('AlertShow6').hide();
	});
	uploadFile = function(){
		//console.log("doUpload");
		
		dojo.byId("fileToUpload").innerHTML = "uploading...";
		f0.upload();
	}
}
