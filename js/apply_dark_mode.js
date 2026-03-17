const fs = require('fs');
let content = fs.readFileSync('c:/xampp/htdocs/Lease/index.php', 'utf8');

// Colors
content = content.replace(/\btext-slate-800(?! dark:)\b/g, 'text-slate-800 dark:text-slate-100');
content = content.replace(/\btext-slate-700(?! dark:)\b/g, 'text-slate-700 dark:text-slate-200');
content = content.replace(/\btext-slate-600(?! dark:)\b/g, 'text-slate-600 dark:text-slate-300');
content = content.replace(/\btext-slate-500(?! dark:)\b/g, 'text-slate-500 dark:text-slate-400');

// Backgrounds
content = content.replace(/\bbg-white\/80(?! dark:)\b/g, 'bg-white/80 dark:bg-slate-800/90');
content = content.replace(/\bbg-white\/70(?! dark:)\b/g, 'bg-white/70 dark:bg-slate-800/80');
content = content.replace(/\bbg-white\/60(?! dark:)\b/g, 'bg-white/60 dark:bg-slate-800/70');
content = content.replace(/\bbg-white\/40(?! dark:)\b/g, 'bg-white/40 dark:bg-slate-800/50');
content = content.replace(/\bbg-white(?! dark:)\b/g, 'bg-white dark:bg-slate-800');

// Borders
content = content.replace(/\bborder-slate-200\/60(?! dark:)\b/g, 'border-slate-200/60 dark:border-slate-700');
content = content.replace(/\bborder-slate-200\/50(?! dark:)\b/g, 'border-slate-200/50 dark:border-slate-700');
content = content.replace(/\bborder-slate-200(?! dark:)\b/g, 'border-slate-200 dark:border-slate-700');
content = content.replace(/\bborder-slate-100\/50(?! dark:)\b/g, 'border-slate-100/50 dark:border-slate-700');
content = content.replace(/\bborder-slate-100(?! dark:)\b/g, 'border-slate-100 dark:border-slate-700');

// Bg Slate
content = content.replace(/\bbg-slate-50(?! dark:)\b/g, 'bg-slate-50 dark:bg-slate-800');
content = content.replace(/\bbg-slate-100(?! dark:)\b/g, 'bg-slate-100 dark:bg-slate-700');
content = content.replace(/\bbg-slate-200(?! dark:)\b/g, 'bg-slate-200 dark:bg-slate-600');
content = content.replace(/\bbg-slate-50\/50(?! dark:)\b/g, 'bg-slate-50/50 dark:bg-slate-800/50');

// Indigo bgs that might need to be darker
content = content.replace(/\bbg-blue-50\/50(?! dark:)\b/g, 'bg-blue-50/50 dark:bg-blue-900/40');
content = content.replace(/\bbg-blue-50\/30(?! dark:)\b/g, 'bg-blue-50/30 dark:bg-blue-900/20');
content = content.replace(/\bbg-blue-50(?! dark:)\b/g, 'bg-blue-50 dark:bg-blue-900/40');
content = content.replace(/\bbg-blue-100(?! dark:)\b/g, 'bg-blue-100 dark:bg-blue-900/60');

// hover colors
content = content.replace(/\bhover:text-slate-800(?! dark:)\b/g, 'hover:text-slate-800 dark:hover:text-slate-100');
content = content.replace(/\bhover:text-slate-700(?! dark:)\b/g, 'hover:text-slate-700 dark:hover:text-slate-200');
content = content.replace(/\bhover:bg-slate-50(?! dark:)\b/g, 'hover:bg-slate-50 dark:hover:bg-slate-700');
content = content.replace(/\bhover:bg-slate-100(?! dark:)\b/g, 'hover:bg-slate-100 dark:hover:bg-slate-700');
content = content.replace(/\bhover:bg-blue-50(?! dark:)\b/g, 'hover:bg-blue-50 dark:hover:bg-blue-900/60');
content = content.replace(/\bhover:bg-white\/50(?! dark:)\b/g, 'hover:bg-white/50 dark:hover:bg-slate-700/50');

fs.writeFileSync('c:/xampp/htdocs/Lease/index.php', content);
console.log('Success');
