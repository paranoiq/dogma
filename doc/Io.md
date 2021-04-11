
Dogma\Io
========

Main classes:
-------------

- **Io** (static) - filesystem toolbox. basic file and directory operations
- **FileInfo** - file/directory path and operations on that
    - **LinkInfo** - symbolic link path and operations on that
- **File** - open file in binary mode
    - **TextFile** - open file in text mode (lines, CSV)

Exception hierarchy:
--------------------

- **IoException**
    - **FilesystemException**
        - **FileAlreadyExistsException**
        - **FileDoesNotExistException**
        - **FilePermissionsException**
        - **FileLockingException**
    - **StreamException**
    - **IniException**
    - **ContentTypeDetectionException**