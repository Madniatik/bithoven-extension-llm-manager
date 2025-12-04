/**
 * Monitor System v2.0 - Quick Debug Script
 * 
 * Paste this entire script in browser console at:
 * http://localhost:8000/admin/llm/quick-chat
 * 
 * Last Updated: 4 de diciembre de 2025, 18:20
 */

console.clear();
console.log('%c━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'color: cyan; font-weight: bold;');
console.log('%c   MONITOR SYSTEM v2.0 - QUICK DIAGNOSTIC', 'color: cyan; font-weight: bold; font-size: 14px;');
console.log('%c━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'color: cyan; font-weight: bold;');
console.log('');

// TEST 1: Global Objects
console.log('%c[TEST 1] Global Objects', 'color: yellow; font-weight: bold;');
const factoryExists = typeof window.LLMMonitorFactory !== 'undefined';
const adapterExists = typeof window.LLMMonitor !== 'undefined';
const helperExists = typeof window.initLLMMonitor !== 'undefined';

console.log('  LLMMonitorFactory:', factoryExists ? '✅ EXISTS' : '❌ MISSING');
console.log('  LLMMonitor (Adapter):', adapterExists ? '✅ EXISTS' : '❌ MISSING');
console.log('  initLLMMonitor():', helperExists ? '✅ EXISTS' : '❌ MISSING');

if (factoryExists) {
    console.log('  Factory object:', window.LLMMonitorFactory);
}
if (adapterExists) {
    console.log('  Adapter object:', window.LLMMonitor);
}
console.log('');

// TEST 2: DOM Elements
console.log('%c[TEST 2] Monitor Element in DOM', 'color: yellow; font-weight: bold;');
const monitorEl = document.querySelector('.llm-monitor');
if (monitorEl) {
    const monitorId = monitorEl.dataset.monitorId;
    console.log('  Monitor element: ✅ FOUND');
    console.log('  Monitor ID:', monitorId);
    console.log('  Element:', monitorEl);
    
    // Check if visible
    const isVisible = monitorEl.offsetParent !== null;
    console.log('  Is visible:', isVisible ? '✅ YES' : '❌ NO (display:none or hidden)');
} else {
    console.log('  Monitor element: ❌ NOT FOUND');
    console.log('  Searching for monitor pane...');
    const monitorPane = document.querySelector('[id*="split-monitor-pane"]');
    if (monitorPane) {
        console.log('  Monitor pane found:', monitorPane);
        console.log('  Monitor pane display:', window.getComputedStyle(monitorPane).display);
    }
}
console.log('');

// TEST 3: Factory Instances
console.log('%c[TEST 3] Factory Instances', 'color: yellow; font-weight: bold;');
if (factoryExists) {
    const activeInstances = window.LLMMonitorFactory.getActiveInstances();
    console.log('  Active instances:', activeInstances);
    console.log('  Count:', activeInstances.length);
    
    if (monitorEl) {
        const monitorId = monitorEl.dataset.monitorId;
        const instance = window.LLMMonitorFactory.get(monitorId);
        console.log('  Instance for', monitorId + ':', instance ? '✅ EXISTS' : '❌ NOT FOUND');
        if (instance) {
            console.log('  Instance object:', instance);
        }
        
        // Check mismatch
        if (activeInstances.length > 0 && activeInstances[0] !== monitorId) {
            console.warn('  ⚠️ MISMATCH DETECTED!');
            console.warn('    DOM monitorId:', monitorId);
            console.warn('    Factory instances:', activeInstances);
        }
    }
} else {
    console.log('  ❌ Cannot check - Factory not loaded');
}
console.log('');

// TEST 4: Alpine.js
console.log('%c[TEST 4] Alpine.js State', 'color: yellow; font-weight: bold;');
const alpineLoaded = typeof Alpine !== 'undefined';
console.log('  Alpine.js loaded:', alpineLoaded ? '✅ YES' : '❌ NO');

if (monitorEl && alpineLoaded) {
    const alpineData = Alpine.$data(monitorEl);
    console.log('  Alpine data on monitor:', alpineData);
}

// Check workspace
const workspace = document.querySelector('[data-session-id]');
if (workspace) {
    console.log('  Workspace element: ✅ FOUND');
    console.log('  Session ID:', workspace.dataset.sessionId);
    if (alpineLoaded) {
        const workspaceData = Alpine.$data(workspace);
        console.log('  Workspace Alpine data:', workspaceData);
        if (workspaceData) {
            console.log('  monitorOpen:', workspaceData.monitorOpen);
        }
    }
}
console.log('');

// TEST 5: Manual Initialization
console.log('%c[TEST 5] Manual Initialization Attempt', 'color: yellow; font-weight: bold;');
if (monitorEl && helperExists) {
    const monitorId = monitorEl.dataset.monitorId;
    console.log('  Attempting: window.initLLMMonitor("' + monitorId + '")');
    try {
        const result = window.initLLMMonitor(monitorId);
        console.log('  Result:', result ? '✅ SUCCESS' : '❌ FAILED');
        if (result) {
            console.log('  Monitor instance:', result);
        }
    } catch (error) {
        console.error('  ❌ ERROR:', error);
    }
} else {
    if (!monitorEl) console.log('  ❌ Cannot test - Monitor element not found');
    if (!helperExists) console.log('  ❌ Cannot test - initLLMMonitor() not found');
}
console.log('');

// TEST 6: Button Handlers
console.log('%c[TEST 6] Button onclick Handlers', 'color: yellow; font-weight: bold;');
if (monitorEl) {
    const copyBtn = monitorEl.querySelector('[onclick*="copyLogs"]');
    const downloadBtn = monitorEl.querySelector('[onclick*="downloadLogs"]');
    const clearBtn = monitorEl.querySelector('[onclick*="clearLogs"]');
    
    if (copyBtn) {
        console.log('  Copy button: ✅ FOUND');
        console.log('  onclick:', copyBtn.getAttribute('onclick'));
    } else {
        console.log('  Copy button: ❌ NOT FOUND');
    }
    
    if (downloadBtn) {
        console.log('  Download button: ✅ FOUND');
        console.log('  onclick:', downloadBtn.getAttribute('onclick'));
    } else {
        console.log('  Download button: ❌ NOT FOUND');
    }
} else {
    console.log('  ❌ Cannot check - Monitor element not found');
}
console.log('');

// SUMMARY
console.log('%c━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'color: cyan; font-weight: bold;');
console.log('%c   DIAGNOSTIC SUMMARY', 'color: cyan; font-weight: bold; font-size: 14px;');
console.log('%c━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'color: cyan; font-weight: bold;');

let issuesFound = [];

if (!factoryExists) issuesFound.push('LLMMonitorFactory not loaded');
if (!adapterExists) issuesFound.push('LLMMonitor adapter not loaded');
if (!monitorEl) issuesFound.push('Monitor element not in DOM');
if (monitorEl && !monitorEl.offsetParent) issuesFound.push('Monitor element hidden (x-show or display:none)');
if (factoryExists && monitorEl) {
    const monitorId = monitorEl.dataset.monitorId;
    const instance = window.LLMMonitorFactory.get(monitorId);
    if (!instance) issuesFound.push('Monitor instance not created for ID: ' + monitorId);
}

if (issuesFound.length === 0) {
    console.log('%c✅ ALL TESTS PASSED', 'color: green; font-weight: bold; font-size: 16px;');
    console.log('');
    console.log('Monitor should be working. If buttons still fail, check:');
    console.log('  1. Browser console for JavaScript errors');
    console.log('  2. Network tab for failed script loads');
    console.log('  3. SweetAlert2 (Swal) is loaded');
} else {
    console.log('%c❌ ISSUES FOUND:', 'color: red; font-weight: bold; font-size: 16px;');
    issuesFound.forEach((issue, index) => {
        console.log(`  ${index + 1}. ${issue}`);
    });
    console.log('');
    console.log('%cRECOMMENDED ACTIONS:', 'color: orange; font-weight: bold;');
    
    if (!factoryExists || !adapterExists) {
        console.log('  → Check if monitor-api.blade.php is included in page');
        console.log('  → View page source and search for "window.LLMMonitorFactory"');
        console.log('  → Verify vendor:publish ran: php artisan vendor:publish --tag=llm-assets --force');
    }
    
    if (!monitorEl) {
        console.log('  → Monitor element not rendered. Check if $showMonitor is true');
        console.log('  → Check Alpine.js x-show conditions');
        console.log('  → Look for .llm-monitor in Elements tab');
    }
    
    if (monitorEl && !monitorEl.offsetParent) {
        console.log('  → Monitor is in DOM but hidden');
        console.log('  → Check Alpine.js monitorOpen variable');
        console.log('  → Try toggling monitor manually: Alpine.$data(workspace).monitorOpen = true');
    }
    
    if (factoryExists && monitorEl) {
        const monitorId = monitorEl.dataset.monitorId;
        const instance = window.LLMMonitorFactory.get(monitorId);
        if (!instance) {
            console.log('  → Instance not created. Try manual init:');
            console.log('    window.initLLMMonitor("' + monitorId + '")');
            console.log('  → Check if x-init hook executed in monitor component');
            console.log('  → Look for initialization errors in console');
        }
    }
}

console.log('');
console.log('%c━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'color: cyan; font-weight: bold;');
console.log('For full checklist: see docs/MONITOR-DEBUG-CHECKLIST.md');
console.log('%c━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'color: cyan; font-weight: bold;');
