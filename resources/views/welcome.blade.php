<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Job</title>

        <link rel="stylesheet" type="text/css" href="{{ asset('assets/mdb/css/bootstrap.min.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/mdb/css/mdb.min.css') }}">
        <link rel="stylesheet" type="text/css" href="{{ asset('assets/mdb/css/style.min.css') }}">
    </head>
    <body>
        <div class="flex-center position-ref full-height">
            <!-- Default form register -->
            <form class="text-center border border-light p-5" id="form" method="post" enctype="multipart/form-data">
                <p class="h4 mb-4">Submit JOB</p>

                <div class="form-row mb-4">
                    <div class="col">
                        <!-- First name -->
                        <input type="text" name="firstname" class="form-control" placeholder="First name">
                    </div>
                    <div class="col">
                        <!-- Last name -->
                        <input type="text" name="lastname" class="form-control" placeholder="Last name">
                    </div>
                </div>

                <input type="text" name="company" class="form-control mb-4" placeholder="Company name">

                <input type="text" name="site" class="form-control mb-4" placeholder="www.google.com">


                <!-- E-mail -->
                <input type="email" id="email" name="email" class="form-control mb-4" placeholder="E-mail">

                <!-- Phone number -->
                <input type="text" id="phone" name="phone" class="form-control" placeholder="Phone number">
                <!-- Sign up button -->
                <button class="btn btn-info my-4 btn-block" type="submit">Submit job</button>

            </form>
        </div>
        <!-- Modal -->
        <div class="modal fade" id="basicExampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" id="html-msg">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </body>
    <script src="{{ asset('assets/mdb/js/jquery-3.3.1.min.js') }}"></script>
    <script src="{{ asset('assets/mdb/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/mdb/js/mdb.min.js') }}"></script>
    <script src="{{ asset('assets/jQuery-Mask-Plugin-master/dist/jquery.mask.min.js') }}"></script>
    <script src="{{ asset('assets/jquery-validation/js/jquery.validate.min.js') }}"></script>
    <script src="{{ asset('assets/blockui-master/jquery.blockUI.js') }}"></script>
    <script>
        $.fn.serializeObject = function() {
            var o = {};
            var a = this.serializeArray();
            $.each(a, function() {
                if (o[this.name]) {
                    if (!o[this.name].push) {
                        o[this.name] = [o[this.name]];
                    }
                    o[this.name].push(this.value || '');
                } else {
                    o[this.name] = this.value || '';
                }
            });
            return o;
        };
        $.validator.addMethod("maskedPhone", function (phone_number, element) {
            phone_number = phone_number.replace(/\s+/g, "");
            return this.optional(element) != 'dependency-mismatch' && phone_number.length > 9 &&
                phone_number.match(/^((\()?\d{3}(\))?(-|\s)?\d{3}(-|\s)\d{4})|\(_{3}\) (_{3})-(_{4})|\(\s{3}\) (\s{3})-(\s{4})|\(\) -$/);
        }, "Required");
        $(function() {
            var form = $('#form');
            form.validate({
                focusInvalid: true,
                messages: {
                    firstname: 'Input correct first name',
                    lastname: 'Input correct last name',
                    email: 'Input correct email address, example: tormas.jackson@outlook.com',
                    phone: 'Input correct phone number.'
                },
                rules: {
                    firstname: {
                        required: true,
                        minlength: 2
                    },
                    lastname: {
                        required: true,
                        minlength: 2
                    },
                    email: {
                        required: true,
                        email: true
                    },
                    phone: {
                        maskedPhone: true
                    },
                    company: { required: true },
                    site: {
                        required: true,
                        url: true
                    }
                },

                errorPlacement: function(err, element) {
                    element.addClass('is-invalid');
                },

                highlight: function (element) { // hightlight error inputs
                    $(element)
                        .closest('.form-group').addClass('has-error'); // set error class to the control group
                },

                unhighlight: function (element) { // revert the change done by hightlight
                    $(element)
                        .closest('.form-group').removeClass('has-error'); // set error class to the control group
                },

                success: function (label, element) {
                    $(element).removeClass('is-invalid');
                },

                submitHandler: function (form) {
                    var send_data = $(form).serializeObject();
                    send_data._token = '{{ csrf_token() }}';
                    send_data.phone = send_data.phone.replace(/(\()|(\))|(-)|(\s)/g, '');
                    $.blockUI();
                    $.ajax({
                        url: '{{ route('jobsubmit') }}',
                        type: 'post',
                        data: send_data,
                        dataType: 'json',
                        error: function(jqXHR) {
                            $.unblockUI();
                            var msg_html = '';
                            if(jqXHR.responseJSON) {
                                $.each(jqXHR.responseJSON.errors, function() {
                                    msg_html += '<p>'+this[0]+'</p>';
                                });
                            }
                            else {
                                msg_html = 'Server Error.';
                            }
                            $('#html-msg').html(msg_html);
                            $('#basicExampleModal').modal('show');
                        },
                        success: function(data) {
                            $.unblockUI();
                            if(data.success === true) {
                                $('#html-msg').html('Success!');
                            }
                            else {
                                $('#html-msg').html('Sorry! Can not submit job!');
                            }
                            $('#basicExampleModal').modal('show');
                        }
                    });
                }
            });
            $("#phone").mask("(999) 999-9999", {placeholder:"(123) 456-7890"});
        });
    </script>
</html>
