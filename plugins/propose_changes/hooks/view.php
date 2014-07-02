<?php


function HookPropose_changesViewAfterresourceactions()
    {
    global $ref, $search,$offset,$archive,$sort, $order_by, $userref, $edit_access, $propose_changes_always_allow;
    
	if($edit_access)
		{
		$userproposals= sql_value("select count(*) value from propose_changes_data where resource='$ref'",0);
		//print_r($userproposals);
                if ($userproposals>0)
			{
			global $baseurl, $lang;
			?>
			<li><a href="<?php echo $baseurl ?>/plugins/propose_changes/pages/propose_changes.php?ref=<?php echo urlencode($ref)?>&amp;search=<?php echo urlencode($search)?>&amp;search_offset=<?php echo urlencode($offset)?>&amp;order_by=<?php echo urlencode($order_by)?>&amp;sort=<?php echo urlencode($sort)?>&amp;archive=<?php echo urlencode($archive)?>" onClick="return CentralSpaceLoad(this,true);">&gt; <?php echo $lang["propose_changes_review_proposed_changes"]?></a></li>
			<?php 
			}
		}
	else
		{
		if(!$propose_changes_always_allow)
			{
			# Check user has permission.
			$proposeallowed=sql_value("select r.ref value from resource r left join collection_resource cr on r.ref='$ref' and cr.resource=r.ref left join user_collection uc on uc.user='$userref' and uc.collection=cr.collection left join collection c on c.ref=uc.collection where c.propose_changes=1","");
			}

		if($propose_changes_always_allow || $proposeallowed!="")    
			{
			global $baseurl, $lang;
			?>
			<li><a href="<?php echo $baseurl ?>/plugins/propose_changes/pages/propose_changes.php?ref=<?php echo urlencode($ref)?>&amp;search=<?php echo urlencode($search)?>&amp;search_offset=<?php echo urlencode($offset)?>&amp;order_by=<?php echo urlencode($order_by)?>&amp;sort=<?php echo urlencode($sort)?>&amp;archive=<?php echo urlencode($archive)?>" onClick="return CentralSpaceLoad(this,true);">&gt; <?php echo $lang["propose_changes_short"]?></a></li>
			<?php            
			}
		}
	
    }