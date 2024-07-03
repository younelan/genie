<?php

    $dh_category = opendir(".");
 $catcount=0; 
print("<table border=1><tr>");
     while (($file_category = readdir($dh_category)) !== false) //read poem chapter / category
     {  
                     if($file_category<>"." and $file_category<>".." && $file_category<>"icon.sheet.gif")
                     {
                        $catcount++;
			if($catcount%4==0) print("</tr><tr>");
			if($catcount%20==0) print("</tr></table><table><tr>");
			print("<td style=\"width:50\"><img src=$file_category><br/>" . $file_category."</td>");
			}
      }


