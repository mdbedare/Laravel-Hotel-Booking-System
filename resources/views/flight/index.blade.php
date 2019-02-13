@extends('main')

@section('title', '| Flight')

@section('content')
<style>
    .list-unstyled {
        position: absolute;
        z-index: 10;
        background-color: white;
        display: none;
        margin-top: -7px;
        border: 1px solid #b4b4b4;
        padding:7px;
    }

    .list-unstyled li {
        padding: 5px;
        cursor: pointer;
        background-color: #fff;
        border-bottom: 1px solid #d4d4d4;
        border-radius: 2px;
        color: #0b1a27;
    }

    .list-unstyled li:hover {
        background-color: #dddddd;
        font-weight: bold;
    }
</style>

<meta name="csrf-token" content="{{ csrf_token() }}"/>

        <div class="row">
            <div class="col-md-12">
                <h1>Flight Search</h1>
                <hr>
                <form action="{{ route('flight.search') }}" method="POST">
                    {{ csrf_field() }}
                    <div class="form-group">
                        <label name="subject">Country:</label>
                        <input id="country" name="country" class="form-control" type="text" maxlength="30">
                        <ul class="list-unstyled"></ul>
                        
                        <label name="subject">CountryCode:</label>
                        <input id="countrycode" name="countrycode" class="form-control" type="text" maxlength="30" readonly>

                        <label name="subject">Departure  Airport:</label>
                        <select id="departure_airport" name="departure_airport" class="form-control">
                            <option value="HKG">Hong Kong International Airport</option>
                        </select>

                        <label name="subject">Arrival Airport:</label>
                        <select id="airport" name="airport" class="form-control"></select>
                        
                        <label name="subject">Departure & Arrival Date:</label>
                        <div class="form-group">
                            <div class="input-group date" id="datetimepicker">
                                <input class="form-control" type="text" name="daterange" id="daterange" value=""/>
                                <span class="input-group-addon">
                                 <span class="glyphicon-calendar glyphicon"></span>
                               </span>
                            </div>
                        </div>
                    </div>

                    <input type="submit" value="Search" class="btn btn-success">
                </form>
            </div>
        </div>
@endsection

@section('scripts')
<script type="text/javascript" src="https://cdn.jsdelivr.net/jquery/latest/jquery.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script>
    
    //setup before functions
    var typingTimer;                //timer identifier
    var doneTypingInterval = 600;  //time in ms

    //on keyup, start the countdown
    $('#country').keyup(function(){
        
        var keyword = $(this).val();
        var CSRF_TOKEN = $('meta[name="csrf-token"]').attr('content');
        
        clearTimeout(typingTimer);
        if ($('#country').val) {
            typingTimer = setTimeout(function(){

                $.ajax({
                    url: 'searchcountry',
                    async: false,
                    type: 'POST',
                    data: {
                        _token: CSRF_TOKEN,
                        name: keyword,
                    },
                    dataType: 'JSON',
                    beforeSend: function () {
                        $(".list-unstyled").html('');
                        console.log('ajax start');
                    },
                    success: function (data) {
                        //console.log(data);
                        if(data['status'] == 'success'){
                            $.each( data['country'], function( key, value ) {
                                if(key>5){ return false; };
                                $('.list-unstyled').append('' +
                                '<li class="search_opt">' +
                                '<div class="row">' +
                                    '<div class="col-md-12">' +
                                    data['country'][key]["name"] +
                                    '</div>' +
                                '</div><input type="hidden" class="list-code" id="c-'+data['country'][key]["name"]+'" name="code" value="'+data['country'][key]["alpha2Code"]+'"></li>');
                            });
                            
                            $(".list-unstyled").fadeIn();
                        } else {
                            $(".list-unstyled").fadeOut();
                            $('#countrycode').val('');
                            $('#airport').html('');
                        }
                        
                        $(".search_opt").each(function (index) {
                            $(this).on("click", function () {
                                var name = $(this).text();
                                var code = $(this).find('.list-code').val();
                                console.log(code);
                                $('#countrycode').val(code);
                                GetAirports(code,CSRF_TOKEN); //Trigger get airport api
                            });
                        });
                    }
                });

            }, doneTypingInterval);
        }
    });

    function GetAirports(code,token){

        //Ajax call api
        $.ajax({
            url: 'searchairport',
            async: false,
            type: 'POST',
            data: {
                _token: token,
                code: code
            },
            dataType: 'JSON',
            beforeSend: function () {

            },
            success: function (data) {
                //<option value="HKG">Hong Kong International Airport</option>

                $.each( data, function( key, value ) {
                    $('#airport').append('<option value="'+value['code']+'">'+value['name']+'</option>');
                });
            }
        });


    }


    $("#country").focus(function () {
        $("#country").trigger("keyup");
    });

    $("#country").blur(function () {
        $(".list-unstyled").fadeOut();
    });


        $(function() {
          $('input[name="daterange"]').daterangepicker({
            opens: 'right',
            locale:{
                format: 'YYYY-MM-DD'
            },
            minDate: new Date(),
              //startDate: '01/13/2019',
              showDropdowns: true,
              autoApply: true
//              ranges: {
//                  'Today': [moment(), moment()],
//                  'This Month': [moment().startOf('month'), moment().endOf('month')],
//                  'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
//              }
          }, function(start, end, label) {
            console.log("A date selection was made: " + start.format('YYYY-MM-DD') + ' to ' + end.format('YYYY-MM-DD'));

          });
        });
</script>
@endsection