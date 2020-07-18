cd app
call vue build OrderControl.vue -t lib
copy dist\OrderControl.umd.js ..\dist
