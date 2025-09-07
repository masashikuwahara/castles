import axios from 'axios'

export const getAdminMaster = (locale) =>
  Promise.all([
    axios.get(`/api/admin/${locale}/prefectures`),
    axios.get(`/api/admin/${locale}/tags`)
  ]).then(([pref, tags]) => ({ prefectures: pref.data, tags: tags.data }))

export const createPlace = (locale, payload) => {
  const fd = new FormData()
  for (const [k,v] of Object.entries(payload.fields)) {
    if (v !== undefined && v !== null) fd.append(k, v)
  }
  fd.append('translations[ja][name]', payload.t.ja.name || '')
  fd.append('translations[ja][summary]', payload.t.ja.summary || '')
  if (payload.t.ja.slug_localized) fd.append('translations[ja][slug_localized]', payload.t.ja.slug_localized)

  if (payload.t.en?.name) {
    fd.append('translations[en][name]', payload.t.en.name)
    fd.append('translations[en][summary]', payload.t.en.summary || '')
    if (payload.t.en.slug_localized) fd.append('translations[en][slug_localized]', payload.t.en.slug_localized)
  }

  (payload.tags || []).forEach((slug, i)=> fd.append(`tags[${i}]`, slug))

  ;(payload.photos || []).forEach((file, i) => fd.append(`photos[${i}]`, file))
  ;(payload.captions_ja || []).forEach((c, i)=> fd.append(`captions_ja[${i}]`, c || ''))
  ;(payload.captions_en || []).forEach((c, i)=> fd.append(`captions_en[${i}]`, c || ''))
  if (Number.isInteger(payload.cover_index)) fd.append('cover_index', String(payload.cover_index))

  return axios.post(`/api/admin/${locale}/places`, fd, { headers: { 'Content-Type': 'multipart/form-data' } })
}

export const createCultural = (locale, payload) => {
  const fd = new FormData()
  for (const [k,v] of Object.entries(payload.fields)) {
    if (v !== undefined && v !== null) fd.append(k, v)
  }

  // meta は JSON 文字列で送る（配列/オブジェクト）
  if (payload.meta) fd.append('meta', JSON.stringify(payload.meta))

  // translations 同様
  fd.append('translations[ja][name]', payload.t.ja.name || '')
  fd.append('translations[ja][summary]', payload.t.ja.summary || '')
  if (payload.t.ja.slug_localized) fd.append('translations[ja][slug_localized]', payload.t.ja.slug_localized)

  if (payload.t.en?.name) {
    fd.append('translations[en][name]', payload.t.en.name)
    fd.append('translations[en][summary]', payload.t.en.summary || '')
    if (payload.t.en.slug_localized) fd.append('translations[en][slug_localized]', payload.t.en.slug_localized)
  }

  (payload.tags || []).forEach((slug, i)=> fd.append(`tags[${i}]`, slug))
  ;(payload.photos || []).forEach((file, i) => fd.append(`photos[${i}]`, file))
  ;(payload.captions_ja || []).forEach((c, i)=> fd.append(`captions_ja[${i}]`, c || ''))
  ;(payload.captions_en || []).forEach((c, i)=> fd.append(`captions_en[${i}]`, c || ''))
  if (Number.isInteger(payload.cover_index)) fd.append('cover_index', String(payload.cover_index))

  return axios.post(`/api/admin/${locale}/culturals`, fd, { headers: { 'Content-Type': 'multipart/form-data' } })
}
