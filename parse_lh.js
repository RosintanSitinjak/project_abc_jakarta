const fs = require('fs');
const data = JSON.parse(fs.readFileSync('/Users/adit/Lamjaya/manggala-copy/localhost_3000-20260312T125543.json', 'utf8'));

console.log('--- Performance ---');
console.log('Score:', data.categories.performance.score * 100);
for (const auditRef of data.categories.performance.auditRefs) {
  if (auditRef.weight > 0) {
    const audit = data.audits[auditRef.id];
    console.log(`- ${audit.title}: ${audit.displayValue} (Score: ${audit.score})`);
  }
}

console.log('\n--- Opportunities ---');
for (const [id, audit] of Object.entries(data.audits)) {
    if (audit.details && audit.details.type === 'opportunity' && audit.score !== 1 && audit.score !== null) {
        console.log(`- ${audit.title} (Savings: ${audit.displayValue || audit.numericValue + 'ms'})`);
    }
}

console.log('\n--- Accessibility ---');
console.log('Score:', data.categories.accessibility.score * 100);
for (const auditRef of data.categories.accessibility.auditRefs) {
  const audit = data.audits[auditRef.id];
  if (audit.score !== 1 && audit.score !== null) {
    console.log(`- ${audit.title}: ${audit.description}`);
  }
}
