<div
    class="relative flex flex-col rounded-lg border border-gray-200 dark:border-gray-700"
    ondrop="dropHandler(event);"
    ondragover="dragOverHandler(event);"
    ondragleave="dragLeaveHandler(event);"
    ondragenter="dragEnterHandler(event);"
>
    {{-- Drag-over overlay --}}
    <div id="overlay"
        class="w-full h-full absolute top-0 left-0 pointer-events-none z-50 flex flex-col items-center justify-center rounded-lg">
        <svg class="w-12 h-12 mb-3 text-orange-500 fill-current opacity-0 transition-opacity" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
            <path d="M19.479 10.092c-.212-3.951-3.473-7.092-7.479-7.092-4.005 0-7.267 3.141-7.479 7.092-2.57.463-4.521 2.706-4.521 5.408 0 3.037 2.463 5.5 5.5 5.5h13c3.037 0 5.5-2.463 5.5-5.5 0-2.702-1.951-4.945-4.521-5.408zm-7.479-1.092l4 4h-3v4h-2v-4h-3l4-4z" />
        </svg>
        <p class="text-orange-500 font-semibold opacity-0 transition-opacity">Drop files to upload</p>
    </div>

    {{-- Drop zone --}}
    <div class="p-6">
        <div class="border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg py-10 flex flex-col items-center justify-center text-center">
            <svg class="w-10 h-10 mb-3 text-gray-400 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                <path d="M19.479 10.092c-.212-3.951-3.473-7.092-7.479-7.092-4.005 0-7.267 3.141-7.479 7.092-2.57.463-4.521 2.706-4.521 5.408 0 3.037 2.463 5.5 5.5 5.5h13c3.037 0 5.5-2.463 5.5-5.5 0-2.702-1.951-4.945-4.521-5.408zm-7.479-1.092l4 4h-3v4h-2v-4h-3l4-4z" />
            </svg>
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                Drag and drop files here, or
            </p>
            <input id="hidden-input" type="file" multiple class="hidden" />
            <button id="button"
                class="bg-orange-500 text-white text-sm font-semibold py-2 px-4 rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-400 transition">
                Browse Files
            </button>
        </div>

        {{-- File preview list --}}
        <h3 class="mt-6 mb-2 text-sm font-semibold text-gray-700 dark:text-gray-300">Selected Files</h3>
        <ul id="gallery" class="flex flex-wrap gap-2 min-h-16">
            <li id="empty" class="w-full flex flex-col items-center justify-center py-6 text-gray-400 dark:text-gray-600">
                <svg class="w-10 h-10 mb-2 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                    <path d="M19 3H5a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2V5a2 2 0 00-2-2zM5 5h14v8l-3-3-4 4-3-3-4 4V5zm0 14v-2l4-4 3 3 4-4 3 3v4H5z"/>
                </svg>
                <span class="text-sm">No files selected</span>
            </li>
        </ul>
    </div>

    {{-- Footer actions --}}
    <div class="flex justify-end gap-3 px-6 pb-6">
        <button id="cancel"
            class="text-sm font-semibold py-2 px-4 rounded-lg border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none transition">
            Cancel
        </button>
        <button id="submit"
            class="bg-orange-500 text-white text-sm font-semibold py-2 px-4 rounded-lg hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-400 transition">
            Upload
        </button>
    </div>
</div>

{{-- File card templates --}}
<template id="file-template">
    <li class="p-1 w-24 h-24">
        <article tabindex="0"
            class="group w-full h-full rounded-lg bg-gray-100 dark:bg-gray-700 cursor-pointer relative shadow-sm focus:outline-none focus:ring-2 focus:ring-orange-400">
            <img alt="upload preview" class="img-preview hidden w-full h-full object-cover rounded-lg" />
            <section class="flex flex-col rounded-lg text-xs break-words w-full h-full absolute top-0 py-2 px-2">
                <h1 class="flex-1 text-gray-700 dark:text-gray-300 text-xs leading-tight group-hover:text-orange-600 truncate"></h1>
                <div class="flex items-center justify-between">
                    <p class="size text-xs text-gray-500 dark:text-gray-400"></p>
                    <button class="delete focus:outline-none hover:bg-gray-200 dark:hover:bg-gray-600 p-1 rounded text-gray-600 dark:text-gray-400">
                        <svg class="pointer-events-none fill-current w-3 h-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path class="pointer-events-none" d="M3 6l3 18h12l3-18h-18zm19-4v2h-20v-2h5.711c.9 0 1.631-1.099 1.631-2h5.316c0 .901.73 2 1.631 2h5.711z" />
                        </svg>
                    </button>
                </div>
            </section>
        </article>
    </li>
</template>

<template id="image-template">
    <li class="p-1 w-24 h-24">
        <article tabindex="0"
            class="group hasImage w-full h-full rounded-lg cursor-pointer relative shadow-sm text-transparent hover:text-white focus:outline-none focus:ring-2 focus:ring-orange-400">
            <img alt="upload preview" class="img-preview w-full h-full object-cover rounded-lg" />
            <section class="flex flex-col rounded-lg text-xs break-words w-full h-full z-20 absolute top-0 py-2 px-2">
                <h1 class="flex-1"></h1>
                <div class="flex items-center justify-between">
                    <p class="size text-xs"></p>
                    <button class="delete focus:outline-none hover:bg-black/30 p-1 rounded">
                        <svg class="pointer-events-none fill-current w-3 h-3" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24">
                            <path class="pointer-events-none" d="M3 6l3 18h12l3-18h-18zm19-4v2h-20v-2h5.711c.9 0 1.631-1.099 1.631-2h5.316c0 .901.73 2 1.631 2h5.711z" />
                        </svg>
                    </button>
                </div>
            </section>
        </article>
    </li>
</template>

<script>
const fileTempl  = document.getElementById("file-template"),
      imageTempl = document.getElementById("image-template"),
      empty      = document.getElementById("empty");

let FILES = {};

function addFile(target, file) {
    const isImage  = file.type.match("image.*"),
          objectURL = URL.createObjectURL(file);

    const clone = isImage
        ? imageTempl.content.cloneNode(true)
        : fileTempl.content.cloneNode(true);

    clone.querySelector("h1").textContent = file.name;
    clone.querySelector("li").id = objectURL;
    clone.querySelector(".delete").dataset.target = objectURL;
    clone.querySelector(".size").textContent =
        file.size > 1048576
            ? Math.round(file.size / 1048576) + " MB"
            : file.size > 1024
                ? Math.round(file.size / 1024) + " KB"
                : file.size + " B";

    if (isImage) {
        Object.assign(clone.querySelector("img"), { src: objectURL, alt: file.name });
    }

    empty.classList.add("hidden");
    target.prepend(clone);
    FILES[objectURL] = file;
}

const gallery = document.getElementById("gallery"),
      overlay = document.getElementById("overlay");

const hidden = document.getElementById("hidden-input");
document.getElementById("button").onclick = () => hidden.click();
hidden.onchange = (e) => { for (const file of e.target.files) addFile(gallery, file); };

const hasFiles = ({ dataTransfer: { types = [] } }) => types.indexOf("Files") > -1;
let counter = 0;

function dropHandler(ev) {
    ev.preventDefault();
    for (const file of ev.dataTransfer.files) addFile(gallery, file);
    overlay.classList.remove("draggedover");
    counter = 0;
}
function dragEnterHandler(e) {
    e.preventDefault();
    if (!hasFiles(e)) return;
    ++counter && overlay.classList.add("draggedover");
}
function dragLeaveHandler(e) { 1 > --counter && overlay.classList.remove("draggedover"); }
function dragOverHandler(e) { if (hasFiles(e)) e.preventDefault(); }

gallery.onclick = ({ target }) => {
    if (target.classList.contains("delete")) {
        const ou = target.dataset.target;
        document.getElementById(ou).remove();
        if (gallery.children.length === 1) empty.classList.remove("hidden");
        delete FILES[ou];
    }
};

document.getElementById("submit").onclick = () => {
    alert(`Submitted Files:\n${JSON.stringify(Object.keys(FILES).map(k => FILES[k].name))}`);
};

document.getElementById("cancel").onclick = () => {
    while (gallery.children.length > 1) gallery.lastChild.remove();
    gallery.prepend(empty);
    empty.classList.remove("hidden");
    FILES = {};
};
</script>

<style>
    .hasImage:hover section { background-color: rgba(0,0,0,.4); }
    #overlay.draggedover { background-color: rgba(255,255,255,.15); }
    #overlay.draggedover svg,
    #overlay.draggedover p { opacity: 1 !important; }
</style>
