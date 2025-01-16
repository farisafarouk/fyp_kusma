USE fyp_kusma;

-- Insert Programs for PERNAS (agency_id = 3)
INSERT INTO programs (agency_id, name, description, resource_types, eligibility_criteria, loan_amount_range, business_premise, subscription_required, application_link) 
VALUES 
-- Skim Pembiayaan Francasi
(3, 
 'Skim Pembiayaan Francasi', 
 'Franchise financing scheme for Bumiputera individuals to establish or grow franchise businesses.', 
 'Loan', 
 '{"gender":["Male","Female"], "age":["21-60"], "bumiputera_status":["Bumiputera"], "business_type":["Sole Proprietor","Private Limited (SDNBHD)"], "business_experience":["None"]}', 
 '50000 - 100000, 100000 - 150000, 150000 - 250000, 250000 - 500000, 500000 - 1000000, 1000000+', 
 'Both', 
 false, 
 'https://pernas.my/perkhidmatan/pembiayaan/skim-pembiayaan-francaisi/'),

-- Skim Bantuan Pembangunan Francais
(3, 
 'Skim Bantuan Pembangunan Francais', 
 'Franchise development assistance scheme to support businesses through grants.', 
 'Grant', 
 '{"gender":["Male","Female"], "age":["18-40","41-60","Above 60"]}', 
 '10000 - 50000, 50000 - 100000', 
 'None', 
 false, 
 'https://pernas.my/dana-bantuan-pembangunan-francais-dbpf/#toggle-id-2-closed'),

-- Skim Pra-Francaisor & Skim Francais Induk
(3, 
 'Skim Pra-Francaisor & Skim Francais Induk', 
 'Grant scheme to assist master franchise owners and potential franchisors in business expansion.', 
 'Grant', 
 '{"gender":["Male","Female"], "age":["18-40","41-60","Above 60"], "business_type":["Private Limited (SDNBHD)"], "business_experience":["More than 6 months"]}', 
 '50000 - 100000, 100000 - 150000, 150000 - 250000, 250000 - 500000, 500000 - 1000000, 1000000+', 
 'Both', 
 false, 
 'https://pernas.my/perkhidmatan/pembiayaan/francaisor/skim-pra-francais-dan-skim-francaisi-induk/'),

-- Lisensor
(3, 
 'Lisensor', 
 'Loan scheme for licensing business owners to expand their operations.', 
 'Loan', 
 '{"gender":["Male","Female"], "age":["18-40","41-60","Above 60"]}', 
 '10000 - 50000, 50000 - 100000, 100000 - 150000, 150000 - 250000, 250000 - 500000, 500000 - 1000000, 1000000+', 
 'None', 
 false, 
 'https://pernas.my/en/services/financing/licensor/'),

-- Lisensi
(3, 
 'Lisensi', 
 'Loan scheme for licensees to start their business journey with proper funding.', 
 'Loan', 
 '{"gender":["Male","Female"], "age":["21-60"], "business_type":["Sole Proprietor","Private Limited (SDNBHD)"]}', 
 '10000 - 50000, 50000 - 100000, 100000 - 150000, 150000 - 250000, 250000 - 500000, 500000 - 1000000, 1000000+', 
 'Physical', 
 false, 
 'https://pernas.my/en/services/financing/licensee/');
