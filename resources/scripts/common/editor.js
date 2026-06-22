import EditorJS from '@editorjs/editorjs';
import Header from '@editorjs/header';
import Paragraph from '@editorjs/paragraph';
import Checklist from '@editorjs/checklist';
import Quote from '@editorjs/quote';
import Embed from '@editorjs/embed';
import Warning from '@editorjs/warning';
import Code from '@editorjs/code';
import Table from '@editorjs/table';
import ImageTool from '@editorjs/image';
import List from 'editorjs-list';

window.EditorJS = EditorJS;
window.Tools = {
    Header,
    Paragraph,
    List,
    Checklist,
    Quote,
    Embed,
    Warning,
    Code,
    Table,
    Image: ImageTool,
};