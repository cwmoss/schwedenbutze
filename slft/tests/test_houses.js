const fs = require('fs');
const path = require('path');

const housesDir = path.join(__dirname, '..', 'content', 'houses');
const files = fs.readdirSync(housesDir).filter(f => f.endsWith('.json'));

if (files.length === 0) {
  console.error(`❌ FEHLER: Keine Haus-Konfigurationsdateien in ${housesDir} gefunden!`);
  process.exit(1);
}

let errors = 0;
console.log(`🔍 Validiere ${files.length} Haus-Konfigurationen...\n`);

for (const file of files) {
  const filePath = path.join(housesDir, file);
  try {
    const raw = fs.readFileSync(filePath, 'utf8');
    const data = JSON.parse(raw);

    const requiredKeys = ['id', 'title', 'slug', 'landlord', 'blocked_dates'];
    const missing = requiredKeys.filter(k => !(k in data));

    if (missing.length > 0) {
      console.error(`❌ ${file}: Fehlende Pflichtfelder: ${missing.join(', ')}`);
      errors++;
      continue;
    }

    if (!data.landlord.email || !data.landlord.email.includes('@')) {
      console.error(`❌ ${file}: Ungültige oder fehlende Vermieter-E-Mail!`);
      errors++;
      continue;
    }

    const blockedCount = (data.blocked_dates || []).length;
    console.log(`✅ ${file}: OK (Haus: ${data.title}, Vermieter-E-Mail: ${data.landlord.email}, Belegte Zeiträume: ${blockedCount})`);
  } catch (err) {
    console.error(`❌ ${file}: Parsing-Fehler: ${err.message}`);
    errors++;
  }
}

console.log('');
if (errors === 0) {
  console.log('🎉 Alle Haus-Konfigurationen sind syntaktisch korrekt und vollständig!');
  process.exit(0);
} else {
  console.error(`⚠️ ${errors} Fehler gefunden.`);
  process.exit(1);
}
