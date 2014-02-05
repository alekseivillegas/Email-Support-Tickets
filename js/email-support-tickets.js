jQuery(function() {
        if(jQuery('.emailst-table').length != 0) {
            try {
                if(jQuery('#emailst_nic_panel').length > 0) {
                    var myNicEditor = new nicEditor({buttonList : ['fontSize','bold','italic','underline','strikethrough','ul', 'subscript','superscript','image','link','unlink'], iconsPath:estScriptParams.estPluginUrl + "/js/nicedit/nicEditorIcons.gif"});
                    myNicEditor.setPanel("emailst_nic_panel");
                    myNicEditor.addInstance("emailst_initial_message");
                }
                if(jQuery('#emailst_nic_panel2').length > 0) {
                    var myNicEditor2 = new nicEditor({buttonList : ['fontSize','bold','italic','underline','strikethrough','ul', 'subscript','superscript','image','link','unlink'], iconsPath:estScriptParams.estPluginUrl + "/js/nicedit/nicEditorIcons.gif"});
                    myNicEditor2.setPanel("emailst_nic_panel2");
                    myNicEditor2.addInstance("email_st_reply");
                }
            } catch(err) {
                
            }                
            jQuery(".emailst-table").toggle();
            jQuery("#emailst_edit_ticket").toggle();

        }
    });

    function loadTicket(primkey, resolution) {
        if(jQuery('.emailst-table').length != 0) {
            jQuery(".emailst-table").fadeOut("slow");
            jQuery("#emailst_edit_div").fadeOut("slow");
            jQuery("#emailst-new").fadeOut("slow");
            jQuery("#emailst_edit_ticket").fadeIn("slow");
            jQuery("#emailst_edit_ticket_inner").load(estScriptParams.estPluginUrl + "/php/load_ticket.php", {"primkey":primkey});
            jQuery("#emailst_edit_primkey").val(primkey);
            jQuery("html, body").animate({scrollTop: jQuery("#emailst_top_page").offset().top}, 2000);
            if(resolution=="Closed") {
                jQuery("#email_st_reply_editor_table_tr1").fadeOut("slow");
                jQuery("#emailst_submit2").fadeOut("slow");
            }
            if(resolution=="Reopenable") {
                jQuery("#email_st_reply_editor_table_tr1").fadeOut("slow");
                jQuery("#emailst_set_status").val('Closed');
            }  
            if(resolution=="Open") {
                try {
                    jQuery("#emailst_set_status").val('Open');
                } catch (e) {
                    
                }
            }
        }
    }

    function cancelEdit() {
        if(jQuery('.emailst-table').length != 0) {        
            jQuery("#email_st_reply_editor_table_tr1").fadeIn("slow");
            jQuery("#emailst_submit2").fadeIn("slow");
            jQuery("#emailst_edit_div").fadeIn("slow");
            jQuery("#emailst-new").fadeIn("slow");
            jQuery("#emailst_edit_ticket").fadeOut("slow");
            jQuery("#emailst_edit_primkey").val(0);
            jQuery("#email_st_reply").html("");
            jQuery(".nicEdit-main").html("");
            jQuery("#emailst_edit_ticket_inner").html('<center><img src="' + estScriptParams.estPluginUrl + '/images/loading.gif" alt="..." /></center>');
            jQuery("html, body").animate({scrollTop: jQuery("#emailst_top_page").offset().top}, 2000);
        }
    }

	function cancelAdd() {
		if(jQuery('.emailst-table').length != 0) {
            jQuery("#emailst_edit_div").fadeIn("slow");
            jQuery("#emailst-new").fadeIn("slow");
            jQuery(".emailst-table").fadeOut("slow");
            jQuery("html, body").animate({scrollTop: jQuery("#emailst_top_page").offset().top}, 2000);
	}
}