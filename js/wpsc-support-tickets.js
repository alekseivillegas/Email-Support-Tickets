    jQuery(document).ready(function(){
        if(jQuery('.wpscst-table').length != 0) {
            try {
                if(jQuery('#wpscst_nic_panel').length > 0) {
                    var myNicEditor = new nicEditor({buttonList : ['fontSize','bold','italic','underline','strikethrough','ul', 'subscript','superscript','image','link','unlink'], iconsPath:wpscstScriptParams.estPluginUrl + "/js/nicedit/nicEditorIcons.gif"});
                    myNicEditor.setPanel("wpscst_nic_panel");
                    myNicEditor.addInstance("emailst_initial_message");
                }
                if(jQuery('#wpscst_nic_panel2').length > 0) {
                    var myNicEditor2 = new nicEditor({buttonList : ['fontSize','bold','italic','underline','strikethrough','ul', 'subscript','superscript','image','link','unlink'], iconsPath:wpscstScriptParams.estPluginUrl + "/js/nicedit/nicEditorIcons.gif"});
                    myNicEditor2.setPanel("wpscst_nic_panel2");
                    myNicEditor2.addInstance("wpscst_reply");
                }
            } catch(err) {
                
            }                
            jQuery(".wpscst-table").toggle();
            jQuery("#wpscst_edit_ticket").toggle();

        }
    });

    function loadTicket(primkey, resolution) {
        if(jQuery('.wpscst-table').length != 0) {
            jQuery(".wpscst-table").fadeOut("slow");
            jQuery("#wpscst_edit_div").fadeOut("slow");
            jQuery("#wpscst-new").fadeOut("slow");
            jQuery("#wpscst_edit_ticket").fadeIn("slow");
            jQuery("#wpscst_edit_ticket_inner").load(wpscstScriptParams.estPluginUrl + "/php/load_ticket.php", {"primkey":primkey});
            jQuery("#emailst_edit_primkey").val(primkey);
            jQuery("html, body").animate({scrollTop: jQuery("#wpscst_top_page").offset().top}, 2000);
            if(resolution=="Closed") {
                jQuery("#wpscst_reply_editor_table_tr1").fadeOut("slow");
                jQuery("#wpscst_submit2").fadeOut("slow");
            }
            if(resolution=="Reopenable") {
                jQuery("#wpscst_reply_editor_table_tr1").fadeOut("slow");
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
        if(jQuery('.wpscst-table').length != 0) {        
            jQuery("#wpscst_reply_editor_table_tr1").fadeIn("slow");
            jQuery("#wpscst_submit2").fadeIn("slow");
            jQuery("#wpscst_edit_div").fadeIn("slow");
            jQuery("#wpscst-new").fadeIn("slow");
            jQuery("#wpscst_edit_ticket").fadeOut("slow");
            jQuery("#emailst_edit_primkey").val(0);
            jQuery("#wpscst_reply").html("");
            jQuery(".nicEdit-main").html("");
            jQuery("#wpscst_edit_ticket_inner").html('<center><img src="' + wpscstScriptParams.estPluginUrl + '/images/loading.gif" alt="..." /></center>');
            jQuery("html, body").animate({scrollTop: jQuery("#wpscst_top_page").offset().top}, 2000);
        }
    }

    function cancelAdd() {
        if(jQuery('.wpscst-table').length != 0) {
            jQuery("#wpscst_edit_div").fadeIn("slow");
            jQuery("#wpscst-new").fadeIn("slow");
            jQuery(".wpscst-table").fadeOut("slow");
            jQuery("html, body").animate({scrollTop: jQuery("#wpscst_top_page").offset().top}, 2000);
        }
    }