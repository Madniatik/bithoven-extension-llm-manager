# Monitor System v2.0 - Debug Checklist

**Date:** 4 de diciembre de 2025  
**Issue:** Monitor no recibe datos del streaming  
**Error:** `[LLMMonitor] No monitor instance found for session: monitor-31`

---

## 🔍 Browser Console Commands (Copy & Paste)

### **Test 1: Verify Global Objects Exist**

```javascript
// Should all return objects, not undefined
console.log('LLMMonitorFactory:', window.LLMMonitorFactory);
console.log('LLMMonitor (Adapter):', window.LLMMonitor);
console.log('initLLMMonitor helper:', window.initLLMMonitor);
```

**Expected:**
```
LLMMonitorFactory: MonitorFactory {instances: {…}}
LLMMonitor (Adapter): {_currentSessionId: null, _debugMode: true, ...}
initLLMMonitor helper: ƒ (sessionId)
```

---

### **Test 2: Find Monitor Element in DOM**

```javascript
// Find monitor element
const monitorEl = document.querySelector('.llm-monitor');
console.log('Monitor element:', monitorEl);
console.log('Monitor ID:', monitorEl?.dataset.monitorId);
```

**Expected:**
```
Monitor element: <div class="llm-monitor" data-monitor-id="31">
Monitor ID: "31"  // NOT "monitor-31"
```

⚠️ **If you see "monitor-31"**, there's a prefix being added somewhere.

---

### **Test 3: Check Factory Instances**

```javascript
// List all initialized monitors
console.log('Active instances:', window.LLMMonitorFactory.getActiveInstances());

// Try to get specific instance
const sessionId = document.querySelector('.llm-monitor')?.dataset.monitorId;
console.log('Session ID from DOM:', sessionId);
console.log('Monitor instance:', window.LLMMonitorFactory.get(sessionId));
```

**Expected:**
```
Active instances: ["31"]  // or ["default"]
Session ID from DOM: "31"
Monitor instance: MonitorInstance {sessionId: "31", storage: ...}
```

⚠️ **If `Active instances: []`**, monitor was not initialized.

---

### **Test 4: Manual Initialization**

```javascript
// If monitor not initialized, try manual init
const sessionId = document.querySelector('.llm-monitor')?.dataset.monitorId;
console.log('Attempting manual init for:', sessionId);
const monitor = window.initLLMMonitor(sessionId);
console.log('Manual init result:', monitor);
```

**Expected:**
```
[LLMMonitor] Manually initialized monitor: 31
Manual init result: MonitorInstance {sessionId: "31", ...}
```

---

### **Test 5: Test Adapter Methods**

```javascript
// Get sessionId
const sessionId = document.querySelector('.llm-monitor')?.dataset.monitorId;

// Test adapter calls (should NOT show warnings)
window.LLMMonitor.start(sessionId);
window.LLMMonitor.trackChunk('Test chunk', 5, sessionId);
window.LLMMonitor.complete('github', 'claude-sonnet-4.5', sessionId);
```

**Expected console logs:**
```
[LLMMonitor] Started: 31
```

⚠️ **If you see `[LLMMonitor] No monitor instance found`**, instance is not created.

---

### **Test 6: Check Alpine.js Timing**

```javascript
// Check if Alpine.js has initialized
console.log('Alpine.js:', typeof Alpine !== 'undefined' ? 'Loaded' : 'Not loaded');

// Check monitor visibility (x-show)
const monitorPane = document.getElementById('split-monitor-pane-31');  // Replace 31 with your session ID
console.log('Monitor pane:', monitorPane);
console.log('Monitor visible:', monitorPane?.style.display !== 'none');
```

**Expected:**
```
Alpine.js: "Loaded"
Monitor pane: <div class="split-pane split-monitor" id="split-monitor-pane-31">
Monitor visible: true  // Should be true when monitor is open
```

---

### **Test 7: Streaming Handler Integration**

```javascript
// Check if streaming handler exists
console.log('Streaming Handler:', window.LLMStreamingHandler);

// Simulate streaming start (ONLY RUN DURING ACTUAL STREAMING)
const sessionId = document.querySelector('.llm-monitor')?.dataset.monitorId;
window.LLMMonitor.start(sessionId);
window.LLMMonitor.trackChunk('Hello world', 10, sessionId);
```

**Expected:**
```
Streaming Handler: {eventSource: null, isStreaming: false, ...}
[LLMMonitor] Started: 31
```

---

### **Test 8: Verify Button Click Handlers**

```javascript
// Get monitor buttons and test onclick
const monitorEl = document.querySelector('.llm-monitor');
const sessionId = monitorEl?.dataset.monitorId;
const copyBtn = monitorEl?.querySelector('[onclick*="copyLogs"]');

console.log('Copy button:', copyBtn);
console.log('Button onclick:', copyBtn?.getAttribute('onclick'));

// Simulate click (should NOT show error)
if (copyBtn) {
    eval(copyBtn.getAttribute('onclick'));
}
```

**Expected onclick attribute:**
```
window.LLMMonitor.copyLogs('31')  // NOT 'monitor-31'
```

---

## 🐛 Common Issues & Fixes

### Issue 1: `Active instances: []` (No instances created)

**Cause:** Monitor initialization didn't run

**Fix:**
```javascript
// Manual initialization
const sessionId = document.querySelector('.llm-monitor')?.dataset.monitorId;
window.initLLMMonitor(sessionId);
```

**Permanent Fix:**
- Verify `monitor-api.blade.php` loads before `monitor.blade.php`
- Check Alpine.js `x-init` hook is executing

---

### Issue 2: `data-monitor-id="monitor-31"` (Wrong ID format)

**Cause:** Prefix being added somewhere in Blade

**Investigation:**
```bash
# Search for 'monitor-' prefix in Blade files
grep -r "monitor-{{" resources/views/
grep -r "'monitor-'" resources/views/
```

**Fix:** Remove prefix from Blade template

---

### Issue 3: `[LLMMonitor] No monitor instance found for session: X`

**Cause:** sessionId mismatch between button onclick and factory

**Debugging:**
```javascript
// Compare IDs
const domId = document.querySelector('.llm-monitor')?.dataset.monitorId;
const factoryIds = window.LLMMonitorFactory.getActiveInstances();
console.log('DOM ID:', domId);
console.log('Factory IDs:', factoryIds);
console.log('Match:', factoryIds.includes(domId) ? 'YES' : 'NO ❌');
```

**Fix:** Ensure both use same ID format (no prefixes)

---

### Issue 4: Buttons don't respond (no console logs)

**Cause:** JavaScript error preventing execution

**Debugging:**
```javascript
// Check for errors in console
// Look for red errors BEFORE clicking button

// Test if Swal (SweetAlert2) is loaded
console.log('Swal:', typeof Swal !== 'undefined' ? 'Loaded' : 'Not loaded ❌');
```

**Fix:**
- Ensure SweetAlert2 (`Swal`) is loaded
- Check for JavaScript syntax errors
- Verify button onclick attribute is correct

---

### Issue 5: Monitor initializes but metrics don't update

**Cause:** Streaming handler not calling adapter methods

**Debugging:**
```javascript
// Enable debug mode
window.LLMMonitor._debugMode = true;

// Send a message in chat
// Watch console for:
// [LLMMonitor] Started: X
// [LLMMonitor] trackChunk called (no log for this currently)
// [LLMMonitor] Completed: X
```

**Fix:**
- Verify `streaming-handler.blade.php` passes `params.sessionId`
- Check that `LLMStreamingHandler.start()` receives sessionId parameter

---

## 📊 Expected Flow (Normal Operation)

1. **Page Load:**
   ```
   [LLMMonitor] Auto-initialized monitor: 31
   ```

2. **User Sends Message:**
   ```
   [LLMMonitor] Started: 31
   ```

3. **Streaming Chunks Arrive:**
   ```
   (No console logs unless debug enabled)
   Stats update in UI (tokens, chunks, duration)
   ```

4. **Stream Completes:**
   ```
   [LLMMonitor] Completed: 31
   Activity history row added
   ```

5. **User Clicks Copy Button:**
   ```
   (No console logs)
   SweetAlert2 popup: "Logs copied to clipboard"
   ```

---

## 🔧 Next Steps

1. **Run Tests 1-3** to verify basic setup
2. **If Test 3 fails (no instances)**, run Test 4 (manual init)
3. **If manual init works**, issue is initialization timing
4. **If manual init fails**, issue is in MonitorFactory or DOM structure
5. **Run Test 5** to verify adapter works after initialization
6. **Send test message** and verify metrics update
7. **Check Laravel logs** for PHP debug messages about monitorId

---

## 📝 Report Template

```
## Monitor Debug Report

**Session ID from DOM:** `___`
**Active Instances:** `[___]`
**Match:** YES / NO
**Monitor Initialized:** YES / NO
**Alpine.js Loaded:** YES / NO
**Swal Loaded:** YES / NO
**Console Errors:** ___

**Test Results:**
- Test 1 (Global Objects): PASS / FAIL
- Test 2 (DOM Element): PASS / FAIL
- Test 3 (Factory Instances): PASS / FAIL
- Test 4 (Manual Init): PASS / FAIL / N/A
- Test 5 (Adapter Methods): PASS / FAIL
- Test 6 (Alpine Timing): PASS / FAIL
- Test 7 (Streaming): PASS / FAIL / N/A
- Test 8 (Button Handlers): PASS / FAIL

**Issue:** ___
**Fix Applied:** ___
```

---

Copy this entire markdown to a file and run the tests in browser console at `http://localhost:8000/admin/llm/quick-chat` 🚀
