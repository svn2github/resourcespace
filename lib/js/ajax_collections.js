// Functions to support collections.

 // Prevent caching
 jQuery.ajaxSetup({ cache: false });
 
function PopCollection(thumbs) {
	if(thumbs == "hide" && collections_popout) {
		ToggleThumbs();
	}
}

function ChangeCollection(collection,k)
	{
	console.log("changecollection");
	thumbs=getCookie("thumbs");
	PopCollection(thumbs);
	// Set the collection and update the count display
	jQuery('#CollectionDiv').load(baseurl_short + 'pages/collections.php?collection=' + collection+'&thumbs='+thumbs+'&k=' + k);
	}
	
function UpdateCollectionDisplay(k)
	{
		
	thumbs=getCookie("thumbs");
	PopCollection(thumbs);
	// Update the collection count display
	jQuery('#CollectionDiv').load(baseurl_short + 'pages/collections.php?thumbs='+thumbs+'&k=' + k);
	}

function AddResourceToCollection(event,resource,size)
	{	
	if(event.shiftKey==true)
		{
		if (typeof prevadded != 'undefined')
			{
			lastchecked=jQuery('#check' + prevadded)
			if (lastchecked.length!=0) 
				{
				var resourcelist=new Array();
				addresourceflag=false;
				jQuery('.checkselect').each(function () {
					if(jQuery(this).attr("id")==lastchecked.attr("id"))
						{
						if(addresourceflag==false) 	 // Set flag to mark start of resources to add
							{					
							addresourceflag=true;				
							}
						else // Clear flag to mark end of resources to add
							{
							addresourceflag=false;	
							}
						}
					else if(jQuery(this).attr("id")=='check'+resource)	
						{
						// Add resource to list before clearing flag
						resourceid=jQuery(this).attr("id").substring(5)
						resourcelist.push(resourceid);
						jQuery(this).attr('checked','checked');
						if(addresourceflag==false)	
							{					
							addresourceflag=true;			
							}
						else
							{
							addresourceflag=false;	
							}		
						}
					if(addresourceflag==true)
						{
						// Add resource to list 
						resourceid=jQuery(this).attr("id").substring(5)
						resourcelist.push(resourceid);
						jQuery(this).attr('checked','checked');
						}
					});		
				resource=resourcelist.join(",");
				}			
			}
		prevadded=resource;
		}
	else
		{
		prevadded=resource;	
		}	

	thumbs=getCookie("thumbs");
	PopCollection(thumbs);
	jQuery('#CollectionDiv').load(baseurl_short + 'pages/collections.php?add=' + resource+'&size='+size+'&thumbs='+thumbs);
	delete prevremoved;
	}
	
function RemoveResourceFromCollection(event,resource,pagename)
	{
	if(event.shiftKey==true)
		{
		if (typeof prevremoved != 'undefined')
			{
			
			lastunchecked=jQuery('#check' + prevremoved)
			if (lastunchecked.length!=0) 
				{
				var resourcelist=new Array();
				removeresourceflag=false;
				jQuery('.checkselect').each(function () {
					if(jQuery(this).attr("id")==lastunchecked.attr("id"))
						{
						if(removeresourceflag==false) 	 // Set flag to mark start of resources to remove
							{					
							removeresourceflag=true;				
							}
						else // Clear flag to mark end of resources to remove
							{
							removeresourceflag=false;	
							}
						}
					else if(jQuery(this).attr("id")=='check'+resource)	
						{
						// Add resource to list before clearing flag
						resourceid=jQuery(this).attr("id").substring(5)
						resourcelist.push(resourceid);
						jQuery(this).removeAttr('checked');
						if(removeresourceflag==false)	
							{					
							removeresourceflag=true;			
							}
						else
							{
							removeresourceflag=false;	
							}		
						}
					if(removeresourceflag==true)
						{
						// Add resource to list to remove
						resourceid=jQuery(this).attr("id").substring(5)
						resourcelist.push(resourceid);
						jQuery(this).removeAttr('checked');
						}
					});		
				resource=resourcelist.join(",");
				}			
			}
		prevremoved=resource;
		}
	else
		{
		prevremoved=resource;	
		}
	thumbs=getCookie("thumbs");
	PopCollection(thumbs);
	jQuery('#CollectionDiv').load( baseurl_short + 'pages/collections.php?remove=' + resource + '&thumbs='+thumbs);
	delete prevadded;
	}
	
	
function UpdateHiddenCollections(checkbox, collection)
	{
	if(checkbox.checked)
		{
		action='showcollection';
		}
	else
		{
		action='hidecollection';
		}
	jQuery.ajax({
		  type: 'POST',
		  url: baseurl_short + 'pages/ajax/showhide_collection.php?action=' + action + '&collection=' + collection,
		  success: function(data) {
				if (data.trim()=="HIDDEN")
						{
						jQuery(checkbox).removeAttr('checked');
						}
				else if (data.trim()=="UNHIDDEN")
						{
						jQuery(checkbox).attr('checked','checked');
						}
				},
		error: function (err) {
				console.log("AJAX error : " + JSON.stringify(err, null, 2));
				if(action=='showcollection')
					{
					jQuery(checkbox).removeAttr('checked');	
					}
				else
					{
					jQuery(checkbox).attr('checked','checked');	
					}
			    }
			});	
	
	}


