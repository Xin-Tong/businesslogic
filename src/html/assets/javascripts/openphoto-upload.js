/**
* Upload utility for OpenPhoto.
* Supports drag/drop with plupload
*/
OPU = (function() {
  var sortByFilename = function(a, b) {
    var aName = a.name;
    var bName = b.name;
    return ((aName < bName) ? -1 : ((aName > bName) ? 1 : 0));
  };
  var active = false, photosUploaded = {success: [], failure: [], duplicate: [], ids: []};
  return {
      init: function() {
        if(typeof plupload === "undefined")
          return;

        var uploaderEl = $("#uploader");
        if(uploaderEl.length === 0)
          return;

        if(typeof(uploaderEl.pluploadQueue) == 'undefined') {
          $("#uploader .insufficient").show();
          return;
        }

        uploaderEl.pluploadQueue({
            // General settings
            runtimes : 'html5',
            url : '/photo/upload.json', // omit 409 since it's somewhat idempotent
            max_file_size : '32mb',
            file_data_name : 'photo',
            //chunk_size : '1mb',
            unique_names : true,
            keep_droptext : true,
            browse_button : 'uploader_browse_alt',
            keep_droptext : window.innerWidth > 480,

            // Specify what files to browse for
            filters : [
                {title : "Photos", extensions : "jpg,jpeg,gif,png"}/*,
                {title : "Videos", extensions : "mov,mp4,webm,ogg"}*/
            ],

            // Flash settings
            flash_swf_url : 'plupload.flash.swf',
            multipart_params:{
              crumb: $("form.upload input.crumb").val()
            },
            preinit: {
              BeforeUpload: function() {
                var uploader = $("#uploader").pluploadQueue();
                $(".upload-progress .total").html(uploader.files.length);
                $(".upload-progress .completed").html(uploader.total.uploaded+1);
                $(".upload-progress").slideDown('fast');
              },
              Error: function(uploader, error) {
                opTheme.message.error('Uh oh, we encountered a problem. ('+error.message+')');
                return false;
              },
              FilesAdded: function(uploader, files) {
                var queue = uploader.files.concat(files);
                if(!active) {
                  active = true;
                  $(window).on('beforeunload', function() { return 'Are you sure you want to leave this page?'; });
                }
                queue.sort(sortByFilename);
                uploader.files = queue;
              },
              FileUploaded: function(uploader, file, response) {
                var apiResponse = $.parseJSON(response.response),
                    code = apiResponse.code,
                    result = apiResponse.result;
                if(code === 201) {
                  OP.Log.info('Successfully uploaded ' + file.name + ' at ' + result.url);
                  photosUploaded.success.push(result);
                  photosUploaded.ids.push(result.id);
                } else if(code === 409) {
                  OP.Log.info('Detected a duplicate of ' + file.name);
                  photosUploaded.duplicate.push(result);
                  photosUploaded.ids.push(result.id);
                } else {
                  OP.Log.error('Unable to upload ' + file.name);
                  photosUploaded.failure.push(file.name);
                }
              },
              UploadComplete: function(uploader, files) {
                var i, file, failed = 0, total = 0, token = $("form.upload input[name='token']").val();
                for(i in files) {
                  if(files.hasOwnProperty(i)) {
                    total++;
                    file = files[i];
                    if(file.status !== plupload.DONE)
                      failed++;
                  }
                  active = false;
                  $(window).off('beforeunload');
                }

                OP.Util.fire('upload:complete-success', photosUploaded);
                if(token.length > 0)
                  OP.Util.fire('upload:send-notification', {token: token, by: OP.Util.config.uploadedBy || '', photosUploaded: photosUploaded});
              },
              UploadFile: function() {
                var uploader = $("#uploader").pluploadQueue(),
                    form = $('form.upload'),
                    license = $("select[name='license'] :selected", form).val(),
                    permission = $("input[name='permission']:checked", form).val(),
                    albums = $("*[name='albums']", form).val(),
                    tags = $("input[name='tags']", form).val(),
                    crumb = $("input[name='crumb']", form).val(),
                    token = $("input[name='token']", form).val(),
                    // http://stackoverflow.com/a/6116631
                    // groups = $("input[name='groups[]']:checked", form).map(function () {return this.value;}).get().join(",");
                    groups = $("select[name='groups']", form).val();

                if(typeof(albums) === "undefined")
                  albums = '';

                if(typeof(groups) === "undefined")
                  groups = '';
                else if(groups !== null)
                  groups = groups.join(',');

                uploader.settings.multipart_params.license = license;
                uploader.settings.multipart_params.tags = tags;
                uploader.settings.multipart_params.permission = permission;
                uploader.settings.multipart_params.albums = albums;
                uploader.settings.multipart_params.groups = groups;
                uploader.settings.multipart_params.crumb = crumb;
                uploader.settings.multipart_params.token = token;
              }
            }
        });
      }
    };
}());
