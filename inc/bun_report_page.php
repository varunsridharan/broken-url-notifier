 <div class="wrap"> 
	<h2>Broken Url Notifier Report</h2>
<table style="margin-top: 25px; float: left;" id="reports" class="details">
    <thead>
        <tr>
            <th>SN</th>
            <th>Type</th>
            <th>URL</th>
            <th>Refered URL</th>
            <th>Hits</th>
            <th>Options</th>
        </tr>
        
    </thead>
    <tbody>


<?php
$i = 1;
foreach($this->broken_reports as $report_key => $report_val){ 
echo '<tr id="'.$report_key.'">
            <td>'.$i.'</td>
            <td>'.$report_val['type'].'</td>
            <td><a href="'.$report_val['url'].'" > '.$report_val['url'].' </a> </td>
            <td><a href="'.$report_val['page'].'" > '.$report_val['page'].'</a> </td>
            <td>'.$report_val['hits'].'</td>
            <td> <input data-delete-key="'.$report_key.'" type="button" class="button button-secondary issue_fixed"  value="Issue Fixed"/> </td>
        </tr>';  
    $i++;
}
?>
        
    </tbody>
</table>


<script>
jQuery(document).ready(function(){
    var table = jQuery('#reports').DataTable({
    "order": [[ 2, "asc" ]],
    "drawCallback": function ( settings ) {
            var api = this.api();
            var rows = api.rows( {page:'current'} ).nodes();
            var last=null;
 
            api.column(1, {page:'current'} ).data().each( function ( group, i ) {
                if ( last !== group ) {
                    jQuery(rows).eq( i ).before(
                        '<tr class="group"><th colspan="6">'+group+'</th></tr>'
                    );
 
                    last = group;
                }
            } );
        }
    });
    
    
    
        jQuery('#reports tbody').on( 'click', 'tr.group', function () {
        var currentOrder = table.order()[0];
        if ( currentOrder[0] === 2 && currentOrder[1] === 'asc' ) {
            table.order( [ 1, 'desc' ] ).draw();
        }
        else {
            table.order( [ 1, 'asc' ] ).draw();
        }
    } );
    
    
    jQuery('input.issue_fixed').click(function(){
        var key = jQuery(this).attr('data-delete-key');
        
        jQuery.ajax({
            url:location.href,
            data:'action=delete_log&key='+key,
            method:'post'
        }).done(function(msg){
            if(msg == 'done'){
                jQuery('tr#'+key).fadeOut('slow',function(){
                    jQuery(this).remove();
                })
            }
        });
    });

});
     
</script>
</div>