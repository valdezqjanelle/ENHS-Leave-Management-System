<template>
  <div class="min-h-screen bg-slate-50 p-4 sm:p-8 space-y-6">
    <header class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
      <h1 class="text-2xl font-bold text-slate-900">Teaching Setup</h1>
      <p class="mt-1 text-sm text-slate-600">Manage grade levels, sections, and department subjects used in teaching assignments.</p>
    </header>

    <div class="flex flex-wrap gap-2">
      <button v-for="item in tabs" :key="item" @click="tab=item" :class="tab===item?'bg-blue-600 text-white':'bg-white text-slate-700 border'" class="rounded-full px-5 py-2 text-sm font-semibold">{{ item }}</button>
    </div>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
      <form v-if="tab==='Grade Levels'" @submit.prevent="saveGrade" class="grid gap-3 md:grid-cols-5">
        <input v-model.trim="gradeForm.grade_name" required placeholder="Grade name" class="control" />
        <select v-model="gradeForm.level" class="control"><option>JHS</option><option>SHS</option></select>
        <input v-model.number="gradeForm.sort_order" type="number" min="0" class="control" placeholder="Order" />
        <label class="check"><input v-model="gradeForm.is_active" type="checkbox" /> Active</label>
        <button class="primary">{{ gradeForm.grade_level_id ? 'Save Changes' : 'Add Grade Level' }}</button>
      </form>

      <form v-if="tab==='Sections'" @submit.prevent="saveSection" class="grid gap-3 md:grid-cols-4">
        <select v-model.number="sectionForm.grade_level_id" required class="control"><option :value="null">Select grade</option><option v-for="g in setup.grade_levels" :key="g.grade_level_id" :value="g.grade_level_id">{{ g.grade_name }} ({{ g.level }})</option></select>
        <input v-model.trim="sectionForm.section_name" required placeholder="Section name" class="control" />
        <label class="check"><input v-model="sectionForm.is_active" type="checkbox" /> Active</label>
        <button class="primary">{{ sectionForm.section_id ? 'Save Changes' : 'Add Section' }}</button>
      </form>

      <form v-if="tab==='Subjects'" @submit.prevent="saveSubject" class="grid gap-3 md:grid-cols-5">
        <select v-model="subjectForm.level" class="control"><option>JHS</option><option>SHS</option></select>
        <select v-model.number="subjectForm.department_id" class="control"><option :value="null">All departments</option><option v-for="d in filteredDepartments" :key="d.department_id" :value="d.department_id">{{ d.department_name }}</option></select>
        <input v-model.trim="subjectForm.subject_name" required placeholder="Subject / specialization" class="control" />
        <label class="check"><input v-model="subjectForm.is_active" type="checkbox" /> Active</label>
        <button class="primary">{{ subjectForm.subject_id ? 'Save Changes' : 'Add Subject' }}</button>
      </form>

      <p v-if="loading" class="py-10 text-center text-slate-500">Loading teaching setup…</p>

      <div v-else class="mt-6 overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="bg-slate-100 text-slate-700"><tr><th class="p-3">Name</th><th class="p-3">Level / Parent</th><th class="p-3">Status</th><th class="p-3 text-right">Actions</th></tr></thead>
          <tbody>
            <tr v-for="item in visibleItems" :key="itemKey(item)" class="border-t">
              <td class="p-3 font-medium text-slate-900">{{ itemName(item) }}</td>
              <td class="p-3 text-slate-600">{{ itemParent(item) }}</td>
              <td class="p-3"><span :class="item.is_active?'text-green-700':'text-slate-500'">{{ item.is_active ? 'Active' : 'Inactive' }}</span></td>
              <td class="p-3 text-right space-x-2"><button @click="editItem(item)" class="secondary">Edit</button><button @click="removeItem(item)" class="danger">Remove</button></td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { getDepartments } from '../services/employee';
import { getTeachingSetup, createGradeLevel, updateGradeLevel, deleteGradeLevel, createSection, updateSection, deleteSection, createSubject, updateSubject, deleteSubject } from '../services/teachingSetup';

const tabs = ['Grade Levels','Sections','Subjects'] as const;
const tab = ref<(typeof tabs)[number]>('Grade Levels');
const loading = ref(false);
const setup = ref<any>({grade_levels:[],subjects:[]});
const departments = ref<any[]>([]);
const gradeForm = ref<any>({grade_level_id:null,grade_name:'',level:'JHS',sort_order:0,is_active:true});
const sectionForm = ref<any>({section_id:null,grade_level_id:null,section_name:'',is_active:true});
const subjectForm = ref<any>({subject_id:null,department_id:null,subject_name:'',level:'JHS',is_active:true});
const sections = computed(() => setup.value.grade_levels.flatMap((g:any)=>(g.sections||[]).map((s:any)=>({...s,grade_level:g}))));
const visibleItems = computed(() => tab.value==='Grade Levels'?setup.value.grade_levels:tab.value==='Sections'?sections.value:setup.value.subjects);
const filteredDepartments = computed(()=>departments.value.filter(d=>d.level===subjectForm.value.level));

const errorMessage=(error:any)=>error?.response?.data?.message||Object.values(error?.response?.data?.errors||{}).flat()[0]||'The operation could not be completed.';
async function load(){loading.value=true;try{[setup.value,departments.value]=await Promise.all([getTeachingSetup(),getDepartments()]);}catch(error){alert(errorMessage(error));}finally{loading.value=false;}}
async function saveGrade(){try{const f=gradeForm.value; f.grade_level_id?await updateGradeLevel(f.grade_level_id,f):await createGradeLevel(f); gradeForm.value={grade_level_id:null,grade_name:'',level:'JHS',sort_order:0,is_active:true};await load();}catch(error){alert(errorMessage(error));}}
async function saveSection(){try{const f=sectionForm.value; f.section_id?await updateSection(f.section_id,f):await createSection(f);sectionForm.value={section_id:null,grade_level_id:null,section_name:'',is_active:true};await load();}catch(error){alert(errorMessage(error));}}
async function saveSubject(){try{const f=subjectForm.value; f.subject_id?await updateSubject(f.subject_id,f):await createSubject(f);subjectForm.value={subject_id:null,department_id:null,subject_name:'',level:'JHS',is_active:true};await load();}catch(error){alert(errorMessage(error));}}
function editItem(i:any){if(tab.value==='Grade Levels')gradeForm.value={...i};else if(tab.value==='Sections')sectionForm.value={section_id:i.section_id,grade_level_id:i.grade_level_id,section_name:i.section_name,is_active:i.is_active};else subjectForm.value={...i};}
async function removeItem(i:any){if(!confirm(`Remove ${itemName(i)}?`))return;try{if(tab.value==='Grade Levels')await deleteGradeLevel(i.grade_level_id);else if(tab.value==='Sections')await deleteSection(i.section_id);else await deleteSubject(i.subject_id);await load();}catch(error){alert(errorMessage(error));}}
const itemKey=(i:any)=>i.grade_level_id??i.section_id??i.subject_id;
const itemName=(i:any)=>i.grade_name??i.section_name??i.subject_name;
const itemParent=(i:any)=>i.grade_level?.grade_name??i.department?.department_name??i.level??'All departments';
onMounted(load);
</script>

<style scoped>
.control{width:100%;border:1px solid #cbd5e1;border-radius:.75rem;padding:.7rem .85rem;color:#0f172a;background:white}.check{display:flex;align-items:center;gap:.5rem;color:#334155}.primary,.secondary,.danger{border-radius:.7rem;padding:.65rem 1rem;font-weight:600}.primary{background:#2563eb;color:white}.secondary{background:#e2e8f0;color:#0f172a}.danger{background:#fee2e2;color:#b91c1c}
</style>
