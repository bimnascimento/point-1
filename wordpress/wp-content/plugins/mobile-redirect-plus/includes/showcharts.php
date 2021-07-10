<?php 
$ipad_count_info = get_option( 'mobi_count_ipad' );
$iphone_count_info = get_option( 'mobi_count_iphone' );
$android_count_info = get_option( 'mobi_count_android' );
$windowsphone_count_info = get_option( 'mobi_count_windowsphone' );
$other_count_info = get_option( 'mobi_count_other' );

if($ipad_count_info==0 && $iphone_count_info==0 && $android_count_info==0 && $windowsphone_count_info==0 && $other_count_info==0){
  echo '<h1 style="background: #408CEA; color: #FFF; padding: 2px; line-height: 36px;">Sorry no Redirect data for showing the chart</h1>';
}
?>

<script type="text/javascript">
    google.load("visualization", "1", {packages:["corechart"]});
    google.setOnLoadCallback(drawChart);
    function drawChart() {
      var data = google.visualization.arrayToDataTable([
        ["Element", "Redirect", { role: "style" } ],
        ["iPad", <?php echo $ipad_count_info;?>, "color: #3366CC"],
        ["iPhone", <?php echo $iphone_count_info;?>, "color: #F2265D"],
        ["Android", <?php echo $android_count_info;?>, "color: #FF9900"],
        ["Windows Phone", <?php echo $windowsphone_count_info;?>, "color: #109618"],
        ["Others", <?php echo $other_count_info;?>, "color: #990099"],
      ]);

      var view = new google.visualization.DataView(data);
      view.setColumns([0, 1, 
                        { calc: "stringify",
                          sourceColumn: 1,
                          type: "string",
                           role: "annotation" },
                        2]);

      var options = {
            title: "Rediret Count for different device",
            width: 900,
            height: 400,
            backgroundColor: 'transparent',
            bar: {groupWidth: "95%"},
            legend: { position: "none"},
            };
      var chart = new google.visualization.BarChart(document.getElementById("barchart_values"));
      chart.draw(view, options);
    }
</script>
<div id="barchart_values" style="width: 900px; height: 400px;"></div>

<script type="text/javascript">
      google.load("visualization", "1", {packages:["corechart"]});
      google.setOnLoadCallback(drawChart);
      function drawChart() {
        var data = google.visualization.arrayToDataTable([
          ['Task', 'Count device Redirect'],
          ['iPad',     <?php echo $ipad_count_info;?>],
          ['iPhone',      <?php echo $iphone_count_info;?>],
          ['Android',  <?php echo $android_count_info;?>],
          ['Windows Phone', <?php echo $windowsphone_count_info;?>],
          ['Others',    <?php echo $other_count_info;?>]
        ]);

        var options = {
          title: '% of Redirection in different device',
          is3D: true,
          backgroundColor: 'transparent',
          height: 600,
        };

        var chart = new google.visualization.PieChart(document.getElementById('piechart_3d'));
        chart.draw(data, options);
      }
    </script>

<div id="piechart_3d" style="width: 900px; height: 500px;background: transparent"></div>

<?php
$setting = (array)get_option('mobi-setting');
@$relink = esc_attr( $setting['redirect_page'] );
if($relink === 'no'){ ?>
  <script type="text/javascript">
        document.getElementById('spacific-page').style.display = 'inline';
</script><?php
}
?>

<script type="text/javascript">
function yesnoCheck() {
    if (document.getElementById('noCheck').checked) {
        document.getElementById('spacific-page').style.display = 'inline';
    }
    else document.getElementById('spacific-page').style.display = 'none';

}
</script>
