cd app
call vue build BatchUpload.vue -t lib
copy dist\BatchUpload.umd.js ..\dist\js
call vue build OrderControl.vue -t lib
copy dist\OrderControl.umd.js ..\dist\js
