INSERT INTO diagnostic_flows (category_id, slug, title, summary, publication_state)
SELECT id, 'pc-no-power', 'PC will not turn on', 'A safe checklist for a desktop or laptop that shows no power or display.', 'published' FROM categories WHERE slug = 'windows'
ON DUPLICATE KEY UPDATE title = VALUES(title), summary = VALUES(summary), publication_state = VALUES(publication_state);
INSERT INTO diagnostic_flow_versions (flow_id, version_number, initial_node_key, publication_state)
SELECT id, 1, 'power_lights', 'published' FROM diagnostic_flows WHERE slug = 'pc-no-power'
ON DUPLICATE KEY UPDATE initial_node_key = VALUES(initial_node_key), publication_state = VALUES(publication_state);
INSERT INTO diagnostic_nodes (version_id,node_key,node_type,input_type,title,prompt,evidence_text,risk_level,estimated_time,required_tools,backup_warning)
SELECT versions.id, seeded.node_key, seeded.node_type, seeded.input_type, seeded.title, seeded.prompt, seeded.evidence, seeded.risk, seeded.time, seeded.tools, seeded.warning FROM diagnostic_flow_versions versions JOIN diagnostic_flows flows ON versions.flow_id=flows.id JOIN (
SELECT 'power_lights' node_key,'question' node_type,'yes_no' input_type,'Do any power lights or fans turn on?' title,'Press the normal power button once. Look and listen without opening the device.' prompt,'Power indicators separate a no-power problem from a display problem.' evidence,'Low' risk,'2 minutes' time,'No tools' tools,'Do not open a power supply or continue if you smell burning.' warning UNION ALL
SELECT 'display_check','question','yes_no','Is the display awake and connected?','Check the monitor power light and cable connection.','A powered PC with no display needs a different safe check.','Low','5 minutes','Display cable','Turn off the device before reconnecting a cable.' UNION ALL
SELECT 'power_source','question','yes_no','Does another wall outlet or known-good charger provide power?','Try one known-good power source only.','A failed outlet, strip, or charger can prevent startup.','Low','5 minutes','Known-good charger or outlet','Do not use damaged cables or adapters.' UNION ALL
SELECT 'outcome_display','outcome',NULL,'Check the display connection','The PC appears to receive power. Check the display input and cable before changing internal hardware.','Power indicators were observed.','Low','10 minutes','Display cable','Back up important work once the display returns.' UNION ALL
SELECT 'outcome_power','outcome',NULL,'Escalate a no-power problem','The device still shows no power after safe external checks. Use official service guidance or a qualified technician.','No safe external power source restored startup.','Medium','15 minutes','No special tools','Do not open a power supply, battery pack, or sealed device.'
) seeded ON flows.slug='pc-no-power'
ON DUPLICATE KEY UPDATE title=VALUES(title),prompt=VALUES(prompt),evidence_text=VALUES(evidence_text);
INSERT INTO diagnostic_options (node_id,option_key,label,evidence_text,next_node_key,sort_order)
SELECT nodes.id, seeded.option_key,seeded.label,seeded.evidence,seeded.next_key,seeded.sort_order FROM diagnostic_nodes nodes JOIN diagnostic_flow_versions versions ON nodes.version_id=versions.id JOIN diagnostic_flows flows ON versions.flow_id=flows.id JOIN (
SELECT 'power_lights' node_key,'yes' option_key,'Yes' label,'Power indicators are present.' evidence,'display_check' next_key,1 sort_order UNION ALL SELECT 'power_lights','no','No','No power indicators are present.','power_source',2 UNION ALL SELECT 'display_check','yes','Yes','The display can receive power.','outcome_display',1 UNION ALL SELECT 'display_check','no','No','The display or cable may be the next safe check.','outcome_display',2 UNION ALL SELECT 'power_source','yes','Yes','External power was restored.','outcome_display',1 UNION ALL SELECT 'power_source','no','No','External power checks did not restore startup.','outcome_power',2
) seeded ON flows.slug='pc-no-power' AND nodes.node_key=seeded.node_key
ON DUPLICATE KEY UPDATE label=VALUES(label),evidence_text=VALUES(evidence_text),next_node_key=VALUES(next_node_key),sort_order=VALUES(sort_order);
INSERT INTO diagnostic_resources (node_id,resource_type,resource_slug,label,sort_order)
SELECT nodes.id,'guide','check-windows-update-issue','Review a safe Windows guide',1 FROM diagnostic_nodes nodes JOIN diagnostic_flow_versions versions ON nodes.version_id=versions.id JOIN diagnostic_flows flows ON versions.flow_id=flows.id WHERE flows.slug='pc-no-power' AND nodes.node_key='outcome_display'
ON DUPLICATE KEY UPDATE label=VALUES(label);
