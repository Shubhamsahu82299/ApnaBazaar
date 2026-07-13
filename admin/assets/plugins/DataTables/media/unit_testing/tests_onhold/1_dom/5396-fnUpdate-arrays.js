// DATA_TEMPLATE: empty_table
oTest.fnStart( "5396 - fnUpdate with 2D arrays for a single row" );

$(document).ready( function () {
	$('#example thead tr').append( '<th>6</th>' );
	$('#example thead tr').append( '<th>7</th>' );
	$('#example thead tr').append( '<th>8</th>' );
	$('#example thead tr').append( '<th>9</th>' );
	$('#example thead tr').append( '<th>10</th>' );
	
	var aDataSet = [
    [
        "1",
        "홍길동",
        "1154315",
        "etc1",
        [
            [ "", "2011-03-04" ],
            [ "", "2009-07-06" ],
            [ "", ",hide" ],
            [ "test5?@naver.com", "" ]
        ],
        "2011-03-04",
        "show"
    ],
    [
        "2",
        "홍길순",
        "2154315",
        "etc2",
        [
            [ "", "2009-09-26" ],
            [ "", "2009-05-21,hide" ], 
            [ "", "2010-03-05" ],
            [ "", ",hide" ],
            [ "", "2010-03-05" ]
        ],
        "2010-03-05",
        "show"
    ]
]
	
    var oTable = $('#example').dataTable({
        "aaData": aDataSet,
        "aoColumns": [
          { "mDataProp": "0"},
          { "mDataProp": "1"},
          { "mDataProp": "2"},
          { "mDataProp": "3"},
          { "mDataProp": "4.0.0"},
          { "mDataProp": "4.0.1"},
          { "mDataProp": "4.1.0"},
          { "mDataProp": "4.1.1"},
          { "mDataProp": "5"},
          { "mDataProp": "6"}
        ]
    });
	
	
	oTest.fnTest( 
		"Initialisation",
		null,
		function () {
			return $('#example tbody tr:eq(0) td:eq(0)').html() == '1';
		}
	);
	
	oTest.fnTest( 
		"Update row",
		function () {
      $('#example').dataTable().fnUpdate( [
          "0",
          "홍길순",
          "2154315",
          "etc2",
          [
              [ "", "2009-09-26" ],
              [ "", "2009-05-21,hide" ], 
              [ "", "2010-03-05" ],
              [ "", ",hide" ],
              [ "", "2010-03-05" ]
          ],
          "2010-03-05",
          "show"
      ], 1 );
		},
		function () {
			return $('#example tbody tr:eq(0) td:eq(0)').html() == '0';
		}
	);
	
	oTest.fnTest( 
		"Original row preserved",
		null,
		function () {
			return $('#example tbody tr:eq(1) td:eq(0)').html() == '1';
		}
	);
	
	
	
	oTest.fnComplete();
} );