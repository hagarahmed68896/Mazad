const fs = require('fs');
const dir = 'C:/Users/eng.Hagar Ahmed/Mazad/resources/views/auth/';
const files = fs.readdirSync(dir);

files.forEach(file => {
    if (!file.endsWith('.blade.php')) return;
    
    let content = fs.readFileSync(dir + file, 'utf8');

    content = content.replace(/#146AF5/g, '#1A73E8');
    content = content.replace(/text-slate-[89]00/g, 'text-[#1C1C1C]');
    content = content.replace(/text-slate-500/g, 'text-[#6F6F6F]');
    content = content.replace(/text-slate-700/g, 'text-[#1C1C1C]');
    content = content.replace(/bg-slate-50/g, 'bg-[#F7F7F7]');
    content = content.replace(/bg-slate-100/g, 'bg-[#EEEEEE]');
    content = content.replace(/bg-\[\#F8FAFC\]/g, 'bg-[#EEEEEE]');
    content = content.replace(/border-slate-200/g, 'border-[#EEEEEE]');
    content = content.replace(/text-slate-400/g, 'text-[#B0B0B0]');

    // Extract the text dynamically from "Right Side: Modern Gradient" to its matching children
    // Using simple substring replacement for the known template structure
    const targetLines = [
        '        <!-- Right Side: Modern Gradient -->',
        '        <div class="hidden lg:flex w-1/2 relative bg-slate-900 overflow-hidden flex-col items-center justify-center p-16 z-0">',
        '            <!-- Modern Mesh Gradient Background -->',
        '            <div class="absolute inset-0 bg-gradient-to-br from-[#1A73E8] via-[#4F46E5] to-[#7C3AED] pointer-events-none z-0"></div>',
        '            <div class="absolute -top-[20%] -right-[10%] w-[70%] h-[70%] rounded-full bg-gradient-to-br from-cyan-400 to-blue-500 opacity-40 blur-[100px] z-0 pointer-events-none"></div>',
        '            <div class="absolute -bottom-[20%] -left-[10%] w-[70%] h-[70%] rounded-full bg-gradient-to-tr from-purple-500 to-indigo-500 opacity-40 blur-[100px] z-0 pointer-events-none"></div>'
    ].join('\n');

    const replacementLines = [
        '        <!-- Right Side: Custom Branded Gradient -->',
        '        <div class="hidden lg:flex w-1/2 relative overflow-hidden flex-col items-center justify-center p-16 z-0" style="background: radial-gradient(73.08% 188.96% at 56.27% 73.08%, #1A73E8 0%, #0E3E76 100%);">'
    ].join('\n');

    // also handle Windows CRLF vs LF
    const normalizedContent = content.replace(/\r\n/g, '\n');
    const newContent = normalizedContent.replace(targetLines, replacementLines);

    fs.writeFileSync(dir + file, newContent);
});

console.log('Figma tokens applied correctly!');
