# TYPO3 File Dashboard
This dashboard helps you get a more simplified view on your files and lets you download them in a more usable format. It can download huge files even better than the regular Filelist can

## Features
### View all your files in one long list
* The dashboard lists every file in your filesystem it can, and display them in a flat hierarchy
* Every file has it's name, path, filetype and creation date listed
  
![image](https://github.com/user-attachments/assets/a053176e-db36-4268-97c9-973ef1bf0cb3)

### Filter your files
* The extension allows you to filter your files by name, path, file type and creation date
* After inserting your filters, press the Filter button to apply them. The Reset button resets all the filters
  
![Filter](https://github.com/user-attachments/assets/141ad82a-911d-4f31-a5ee-ebc087a41e7a)

### Download single files
* Each file has it's own Download button. Clicking it will download the file

![Download](https://github.com/user-attachments/assets/2e883653-680c-4701-9751-3b9c473dfb81)

### Download multiple files 
* If you want to download multiple files at the same time, you can tick their respective checkboxes (1), and then click on the floating action button(2). This will start downloading a zip folder with all your selected files inside

![MultiDownload](https://github.com/user-attachments/assets/62f06c35-affb-4f18-8663-c59acb24a559)

### View file details
* If you click on the name of one of the files, you will be shown a preview of the file and a table with the file's metadata

![image](https://github.com/user-attachments/assets/bac1b5ae-7e02-4dc0-bd3a-cd9446771fac)

## For developers
* The extension triggers a FileRenameEvent when downloading multiple files, which can be used to rename files before they are zipped
