cd app
call vue build BatchUpload.vue -t lib
copy dist\BatchUpload.umd.js ..\dist
