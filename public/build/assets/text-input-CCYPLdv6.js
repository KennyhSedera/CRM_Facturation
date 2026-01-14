import{r as c,j as e}from"./app-BkfbgWqs.js";import{I as g}from"./input-error-k-2dMyci.js";import{a as m,b}from"./index-DIpwLuYT.js";function y({label:n,icon:a,error:s,required:l=!1,type:t="text",className:i="",...o}){const d=t==="password",[r,x]=c.useState(!1);return e.jsxs("div",{className:"",children:[e.jsxs("label",{htmlFor:o.id,className:`
                    mb-2 block text-sm font-semibold
                    text-gray-700 dark:text-gray-300
                `,children:[a&&e.jsx("span",{className:"mr-2 inline-flex h-4 w-4 text-indigo-500 dark:text-indigo-400",children:a}),n,l&&e.jsx("span",{className:"ml-1 text-red-500",children:"*"})]}),e.jsxs("div",{className:"relative",children:[e.jsx("input",{...o,type:d&&r?"text":t,className:`
                        w-full rounded-lg border-2 px-4 py-2.5 pr-12 transition-all
                        bg-white dark:bg-black
                        text-gray-900 dark:text-gray-100
                        placeholder-gray-400 dark:placeholder-gray-500
                        outline-none
                        focus:border-indigo-500
                        dark:focus:border-indigo-400

                        ${s?"border-red-500 dark:border-red-400":"border-gray-300 dark:border-gray-700"}

                        ${i}
                    `}),d&&e.jsx("button",{type:"button",onClick:()=>x(!r),className:`
                            absolute right-3 top-1/2 -translate-y-1/2
                            text-gray-400 dark:text-gray-500
                            hover:text-indigo-600 dark:hover:text-indigo-400
                            focus:outline-none
                        `,"aria-label":r?"Masquer le mot de passe":"Afficher le mot de passe",children:r?e.jsx(m,{}):e.jsx(b,{})})]}),e.jsx(g,{message:s})]})}export{y as T};
